# Tears Claude Worker

Run Claude Code tasks inside isolated Docker containers. Clone any git repo
(or use a local one), give Claude a task, and get back a clean branch with
diffs ready for review. Includes a CLI, a GUI, a manager, and a **web admin
panel** (Laravel) for running everything from a browser.

Works on **macOS** and **Linux**.

## Screenshots

### New Task tab
![New Task tab](screenshots/01-new-task.png)

### Manager tab
![Manager tab](screenshots/02-manager.png)

### Diff Viewer (syntax-highlighted)
![Diff Viewer](screenshots/03-diff-viewer.png)

### Web Admin — New Task
![Web Admin — New Task](screenshots/04-web-new-task.png)

### Web Admin — Completed Runs
![Web Admin — Completed Runs](screenshots/05-web-completed-runs.png)

### Web Admin — Live Tasks
![Web Admin — Live Tasks](screenshots/06-web-live-tasks.png)

### Web Admin — Docker Containers
![Web Admin — Docker](screenshots/07-web-docker.png)

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
- [PHP 8.2+](https://www.php.net/) & [Composer](https://getcomposer.org/) (for the web admin)
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

## Web Admin (Laravel)

A full web-based admin panel built into the Laravel application. Access it
from any browser — perfect for running on a remote server.

### Quick setup

```bash
# 1. Run the setup script (installs deps, configures DB, etc.)
./claude-worker/setup-server.sh

# 2. Start the Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# 3. Open in your browser
# http://your-server:8000/claude-worker
```

### Setup on a web server (Nginx)

For production, point your web server's document root to the `public/`
directory and configure PHP-FPM.

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/axono/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Setup on Apache

Laravel ships with a `public/.htaccess` that handles URL rewriting.
Enable `mod_rewrite` and point the `DocumentRoot` to `public/`.

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/axono/public

    <Directory /path/to/axono/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Pass Authorization header to PHP
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
</VirtualHost>
```

If you don't have access to the vhost config (shared hosting), just upload
the project and point your domain to the `public/` folder. The included
`.htaccess` handles everything.

**Required Apache modules:**

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

### Production `.env`

For either Nginx or Apache, set these in your `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com
```

### Features

- **New Task tab** — configure repo, task, model, options and launch
- **Live Tasks tab** — monitor running tasks with real-time log streaming
- **Completed Runs tab** — browse finished runs, view diffs with syntax
  highlighting, summaries, and logs
- **Docker tab** — see running containers, view logs, stop them
- **API key stored in browser** — never saved on the server
- **Authentication** — protected behind Laravel auth (login required)
- Works from any device with a browser (phone, tablet, laptop)

### Dedicated port (alternative)

If you want the admin on a specific port separate from your main app:

```bash
# Run on port 7080 (or any port you choose)
php artisan serve --host=0.0.0.0 --port=7080

# Or with Nginx, add a second server block on a different port
```

### Access URL

Once running, the admin is at: `http://your-server:PORT/claude-worker`

You must be logged in (register at `/register` first).

---

## GUI

Launch the graphical interface:

```bash
./claude-worker-gui
```

Features:
- **New Task tab** — fill in repo (URL or local), task, options, and run
- **Manager tab** — see running workers, tail logs, stop them, browse completed runs, view diffs with syntax highlighting
- **Diff viewer** — built-in syntax-highlighted diff viewer with "Open External" button
- Auto-detects API key from `ANTHROPIC_API_KEY` environment variable
- Works on macOS and Linux (uses Tkinter, ships with Python)

## Manager (CLI)

Monitor and manage running Tears Claude Worker instances:

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
[tears] [a]ccept / [r]eject / [v]iew diff / [d]iff tool / [p]ush / [t]ransfer
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
├── claude-worker          Main CLI script (bash)
├── claude-worker-gui      Desktop GUI (Python/Tkinter)
├── claude-manager         Instance manager (Python)
├── setup-server.sh        Web server setup script
├── Dockerfile             Container image definition
├── scripts/
│   ├── entrypoint.sh      Runs inside the container
│   └── save-diffs.sh      Generates diff files for review
├── screenshots/           GUI screenshots
├── .gitignore
└── README.md

# Web Admin (integrated into the Laravel app)
app/Http/Controllers/ClaudeWorkerController.php
resources/views/claude-worker/index.blade.php
routes/web.php              (claude-worker/* routes)
```
