# claude-worker

Run Claude Code tasks inside isolated Docker containers. Clone any git repo
(or use a local one), give Claude a task, and get back a clean branch with
diffs ready for review. Includes a CLI, a GUI, and a manager for monitoring
multiple workers.

Works on **macOS** and **Linux**.

## How it works

```
┌──────────────────────────────────────────────────────────────┐
│  Host machine                                                │
│                                                              │
│  $ ./claude-worker --repo <url> --task "do something"        │
│  $ ./claude-worker --local-repo ~/myapp --task "add tests"   │
│       │                                                      │
│       ▼                                                      │
│  ┌───────────────────────────────────────────────────────┐   │
│  │  Docker container (isolated)                          │   │
│  │                                                       │   │
│  │  1. Clone repo (or copy local repo, read-only mount)  │   │
│  │  2. Create feature branch                             │   │
│  │  3. Run Claude Code (auto-accept or interactive)      │   │
│  │  4. Commit changes                                    │   │
│  │  5. Save diffs + git bundle to mounted volume         │   │
│  │                                                       │   │
│  └───────────────────────────────────────────────────────┘   │
│       │                                                      │
│       ▼                                                      │
│  Review prompt: [a]ccept / [r]eject / [v]iew / [d]iff tool   │
│                 [p]ush to remote / [t]ransfer to local repo   │
│       │                                                      │
│       ▼                                                      │
│  output/<run-id>/                                            │
│    ├── diffs/                                                │
│    │   ├── summary.txt       ← human-readable overview       │
│    │   ├── full.patch        ← complete unified diff         │
│    │   ├── stat.txt          ← diffstat                      │
│    │   └── per-file/         ← one .patch per file           │
│    ├── logs/                                                 │
│    │   ├── worker.log        ← full session output           │
│    │   └── claude-output.log ← Claude Code output            │
│    ├── repo.bundle           ← git bundle (for transfer)     │
│    ├── run-info.json         ← input metadata                │
│    └── run-result.json       ← result metadata               │
└──────────────────────────────────────────────────────────────┘
```

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) installed and running
- [Python 3](https://www.python.org/) (for the GUI and manager; Tkinter ships with Python)
- An [Anthropic API key](https://console.anthropic.com/)

## Quick start

```bash
# 1. Set your API key
export ANTHROPIC_API_KEY="sk-ant-..."

# 2. Make scripts executable
chmod +x claude-worker claude-worker-gui claude-manager

# 3. Run a task (CLI)
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Add unit tests for the auth module"

# 4. Or use the GUI
./claude-worker-gui
```

The first run builds the Docker image (cached afterwards). When it finishes
you get a review prompt with accept/reject options.

## CLI Usage

```
./claude-worker --repo <url> --task <prompt> [OPTIONS]
./claude-worker --local-repo <path> --task <prompt> [OPTIONS]
```

### Required (one of)

| Flag | Description |
|------|-------------|
| `--repo <url>` | Git repository URL to clone |
| `--local-repo <path>` | Use a local repo (mounted read-only; changes on a copy) |
| `--task <prompt>` | Task / prompt for Claude Code |

### Options

| Flag | Description | Default |
|------|-------------|---------|
| `--branch <name>` | Feature branch name | `claude/<slugified-task>` |
| `--repo-branch <name>` | Branch to clone from the repo | repo default |
| `--output <dir>` | Output directory for diffs/logs | `./output` |
| `--model <model>` | Claude model to use | API default |
| `--max-turns <n>` | Max agentic turns | unlimited |
| `--interactive` | Prompt before applying changes | off (auto-accept) |
| `--push` | Push the branch to the remote | off |
| `--transfer <path>` | Transfer branch into a local repo (via git bundle) | — |
| `--diff-tool <cmd>` | Open diffs in external tool (code, meld, vimdiff, etc.) | — |
| `--no-review` | Skip accept/reject prompt | off |
| `--rebuild` | Force rebuild the Docker image | — |
| `--verbose` | Detailed output | off |
| `--dry-run` | Show command without executing | — |

### Environment variables

| Variable | Required | Description |
|----------|----------|-------------|
| `ANTHROPIC_API_KEY` | Yes | Your Anthropic API key |

## Examples

### Auto-accept (default)

All changes are automatically accepted inside the container (safe — isolated).
You review the diffs on the host after.

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Refactor the database connection pool to use async/await"
```

### Local repo

Mount a local directory instead of cloning from a URL. The repo is mounted
read-only and copied inside the container, so your working tree is never
modified.

```bash
./claude-worker \
    --local-repo ~/projects/myapp \
    --task "Add input validation to all API endpoints"
```

### Transfer branch (no credentials)

Use git bundles to transfer the branch into your local repo without needing
to push to a remote or pass credentials into the container.

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Add CI pipeline" \
    --transfer ~/projects/myapp
```

### Review with external diff tool

```bash
# VS Code
./claude-worker --local-repo ~/myapp --task "Fix auth bug" --diff-tool code

# Meld (GUI)
./claude-worker --local-repo ~/myapp --task "Fix auth bug" --diff-tool meld

# vimdiff (terminal)
./claude-worker --local-repo ~/myapp --task "Fix auth bug" --diff-tool vimdiff

# macOS FileMerge
./claude-worker --local-repo ~/myapp --task "Fix auth bug" --diff-tool opendiff
```

### Push to remote

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Add OpenAPI docs" \
    --push
```

### Interactive mode

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Migrate from Express to Fastify" \
    --interactive
```

## GUI

Launch the graphical interface:

```bash
./claude-worker-gui
```

Features:
- **New Task tab** — fill in repo (URL or local), task, options, and run
- **Manager tab** — see running workers, tail logs, stop them, browse completed runs, view diffs with syntax highlighting
- Auto-detects API key from `ANTHROPIC_API_KEY` environment variable
- Works on macOS and Linux (uses Tkinter, ships with Python)

## Manager (CLI)

Monitor and manage running Claude worker instances:

```bash
# Interactive TUI
./claude-manager

# Or use subcommands
./claude-manager list              # Running workers
./claude-manager status            # Overview of workers + recent runs
./claude-manager logs <name>       # Tail logs for a running worker
./claude-manager stop <name>       # Stop a worker
./claude-manager stop-all          # Stop all workers
./claude-manager runs              # List completed runs
./claude-manager diff <run-id>     # Show diff from a run
./claude-manager clean --days 7    # Remove old output dirs
```

## Review prompt

After the worker finishes, you get an interactive prompt:

```
[a]ccept / [r]eject / [v]iew diff / [d]iff tool / [p]ush / [t]ransfer
```

| Key | Action |
|-----|--------|
| `a` | Accept changes (diffs saved) |
| `r` | Reject changes (diffs still saved for later) |
| `v` | View full diff in a pager (less) |
| `d` | Open in external diff/merge tool |
| `p` | Accept + push branch to remote |
| `t` | Accept + transfer branch into a local repo |

Use `--no-review` to skip this prompt (e.g. in CI or from the GUI).

## Applying diffs

```bash
# Apply the full patch to your local repo
cd /path/to/your/repo
git apply /path/to/output/<run-id>/diffs/full.patch

# Or import the entire branch (no credentials needed)
./claude-worker ... --transfer /path/to/your/repo
# Then:
git checkout <branch-name>
```

## Security model

- Everything runs inside a Docker container — isolated from your host
- The container has no access to your SSH keys, credentials, or personal config
- Local repos are mounted **read-only** — your files are never modified
- The only output is diffs and git bundles saved to a mounted volume
- Auto-accept (`--dangerously-skip-permissions`) only applies inside the container

## Project structure

```
claude-worker/
├── claude-worker          Main CLI script
├── claude-worker-gui      GUI (Python/Tkinter)
├── claude-manager         Instance manager (Python)
├── Dockerfile             Container image definition
├── scripts/
│   ├── entrypoint.sh      Runs inside the container
│   └── save-diffs.sh      Generates diff files for review
├── .gitignore
└── README.md
```
