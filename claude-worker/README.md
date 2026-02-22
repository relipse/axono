# claude-worker

Run Claude Code tasks inside an isolated Docker container. Clone any git repo,
give Claude a task, and get back a clean branch with diffs ready for review.

Works on **macOS** and **Linux**.

## How it works

```
┌─────────────────────────────────────────────────────────┐
│  Host machine                                           │
│                                                         │
│  $ ./claude-worker --repo <url> --task "do something"   │
│       │                                                 │
│       ▼                                                 │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Docker container                                │   │
│  │                                                  │   │
│  │  1. Clone repo                                   │   │
│  │  2. Create feature branch                        │   │
│  │  3. Run Claude Code (auto-accept or interactive) │   │
│  │  4. Commit changes                               │   │
│  │  5. Save diffs to mounted volume                 │   │
│  │                                                  │   │
│  └──────────────────────────────────────────────────┘   │
│       │                                                 │
│       ▼                                                 │
│  output/<run-id>/                                       │
│    ├── diffs/                                           │
│    │   ├── summary.txt        ← human-readable overview │
│    │   ├── full.patch         ← complete unified diff   │
│    │   ├── stat.txt           ← diffstat                │
│    │   └── per-file/          ← one .patch per file     │
│    ├── logs/                                            │
│    │   ├── worker.log         ← full session output     │
│    │   └── claude-output.log  ← Claude Code output      │
│    ├── run-info.json          ← input metadata          │
│    └── run-result.json        ← result metadata         │
└─────────────────────────────────────────────────────────┘
```

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) installed and running
- An [Anthropic API key](https://console.anthropic.com/)

## Quick start

```bash
# 1. Set your API key
export ANTHROPIC_API_KEY="sk-ant-..."

# 2. Make the script executable
chmod +x claude-worker

# 3. Run a task
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Add unit tests for the auth module"
```

The first run builds the Docker image (cached afterwards). When it finishes,
check the output directory for diffs.

## Usage

```
./claude-worker --repo <url> --task <prompt> [OPTIONS]
```

### Required

| Flag | Description |
|------|-------------|
| `--repo <url>` | Git repository URL to clone |
| `--task <prompt>` | Task / prompt for Claude Code |

### Options

| Flag | Description | Default |
|------|-------------|---------|
| `--branch <name>` | Feature branch name | `claude/<slugified-task>` |
| `--repo-branch <name>` | Branch to clone from the repo | repo default |
| `--output <dir>` | Output directory for diffs/logs | `./output` |
| `--image <name>` | Docker image name | `claude-worker` |
| `--model <model>` | Claude model to use | API default |
| `--max-turns <n>` | Max agentic turns | unlimited |
| `--interactive` | Prompt before applying changes | off (auto-accept) |
| `--rebuild` | Force rebuild the Docker image | — |
| `--verbose` | Detailed output | off |
| `--dry-run` | Show command without executing | — |

### Environment variables

| Variable | Required | Description |
|----------|----------|-------------|
| `ANTHROPIC_API_KEY` | Yes | Your Anthropic API key |

## Examples

### Auto-accept mode (default)

All changes are automatically accepted — safe because everything runs in an
isolated container. Review the diffs afterwards.

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Refactor the database connection pool to use async/await"
```

### Interactive mode

Use `--interactive` to have Claude print proposed changes instead of
auto-applying them.

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Migrate from Express to Fastify" \
    --interactive
```

### Clone a specific branch

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --repo-branch develop \
    --task "Fix the flaky integration tests" \
    --branch claude/fix-flaky-tests
```

### Choose a model

```bash
./claude-worker \
    --repo https://github.com/user/myapp.git \
    --task "Add OpenAPI documentation to all endpoints" \
    --model claude-sonnet-4-20250514
```

## Reviewing diffs

After a run, the output directory contains everything you need:

```bash
# Quick summary of what changed
cat output/<run-id>/diffs/summary.txt

# Diffstat (files + lines changed)
cat output/<run-id>/diffs/stat.txt

# Full unified diff
less output/<run-id>/diffs/full.patch

# Apply the changes to your local repo
cd /path/to/your/repo
git apply /path/to/output/<run-id>/diffs/full.patch

# Or review individual files
ls output/<run-id>/diffs/per-file/
cat output/<run-id>/diffs/per-file/src__auth__login.ts.patch
```

## How auto-accept works

By default, Claude Code runs with `--dangerously-skip-permissions`, which
auto-approves all file edits and command execution. This is safe because:

1. Everything runs inside a Docker container — isolated from your host
2. The container has no access to your files, SSH keys, or credentials
3. No personal config is copied into the container
4. The only output is diffs saved to a mounted volume

If you prefer to review changes interactively, use `--interactive`.

## Project structure

```
claude-worker/
├── claude-worker          Main CLI script (run this)
├── Dockerfile             Container image definition
├── scripts/
│   ├── entrypoint.sh      Runs inside the container
│   └── save-diffs.sh      Generates diff files for review
├── .gitignore
└── README.md
```
