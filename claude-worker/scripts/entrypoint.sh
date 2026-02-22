#!/usr/bin/env bash
#
# entrypoint.sh — Runs inside the Docker container.
# Clones the repo, creates a branch, runs Claude Code, and saves diffs.
#
set -euo pipefail

log()   { printf "\033[1;34m[entrypoint]\033[0m %s\n" "$*"; }
warn()  { printf "\033[1;33m[entrypoint]\033[0m %s\n" "$*" >&2; }
error() { printf "\033[1;31m[entrypoint]\033[0m %s\n" "$*" >&2; exit 1; }

# ── Validate required env vars ───────────────────────────────────────────────
[[ -z "${REPO_URL:-}" ]]        && error "REPO_URL is not set"
[[ -z "${TASK:-}" ]]            && error "TASK is not set"
[[ -z "${BRANCH_NAME:-}" ]]     && error "BRANCH_NAME is not set"
[[ -z "${ANTHROPIC_API_KEY:-}" ]] && error "ANTHROPIC_API_KEY is not set"

AUTO_ACCEPT="${AUTO_ACCEPT:-true}"
VERBOSE="${VERBOSE:-false}"

# Ensure output subdirectories exist
mkdir -p /output/diffs /output/logs

# ── Clone the repository ────────────────────────────────────────────────────
log "Cloning repository: ${REPO_URL}"

CLONE_ARGS=(git clone --depth 100)
if [[ -n "${REPO_BRANCH:-}" ]]; then
    CLONE_ARGS+=(--branch "$REPO_BRANCH")
fi
CLONE_ARGS+=("$REPO_URL" /workspace/repo)

"${CLONE_ARGS[@]}" 2>&1 || error "Failed to clone repository"

cd /workspace/repo

# Configure git for commits (generic, no personal info)
git config user.name "claude-worker"
git config user.email "claude-worker@localhost"

# ── Create feature branch ───────────────────────────────────────────────────
log "Creating branch: ${BRANCH_NAME}"
git checkout -b "$BRANCH_NAME" 2>&1

# Record the starting commit for later diff
START_SHA="$(git rev-parse HEAD)"
echo "$START_SHA" > /output/start-sha.txt

# ── Build Claude Code command ────────────────────────────────────────────────
CLAUDE_ARGS=(claude --print)

if [[ "$AUTO_ACCEPT" == "true" ]]; then
    CLAUDE_ARGS=(claude --dangerously-skip-permissions)
fi

if [[ -n "${CLAUDE_MODEL:-}" ]]; then
    CLAUDE_ARGS+=(--model "$CLAUDE_MODEL")
fi

if [[ -n "${MAX_TURNS:-}" ]]; then
    CLAUDE_ARGS+=(--max-turns "$MAX_TURNS")
fi

# Append the task prompt
CLAUDE_ARGS+=(-p "$TASK")

# ── Run Claude Code ──────────────────────────────────────────────────────────
log "Running Claude Code..."
log "  Mode: $([ "$AUTO_ACCEPT" = "true" ] && echo "auto-accept (--dangerously-skip-permissions)" || echo "print-only")"
log "  Task: ${TASK}"

CLAUDE_EXIT=0
"${CLAUDE_ARGS[@]}" 2>&1 | tee /output/logs/claude-output.log || CLAUDE_EXIT=$?

if [[ $CLAUDE_EXIT -ne 0 ]]; then
    warn "Claude Code exited with code ${CLAUDE_EXIT}"
fi

# ── Stage and commit any changes ─────────────────────────────────────────────
if [[ -n "$(git status --porcelain)" ]]; then
    log "Committing changes..."
    git add -A
    git commit -m "claude-worker: ${TASK}" --no-verify 2>&1 || true
else
    log "No file changes detected"
fi

# ── Save diffs ───────────────────────────────────────────────────────────────
log "Saving diffs for review..."
save-diffs.sh "$START_SHA"

# ── Summary ──────────────────────────────────────────────────────────────────
END_SHA="$(git rev-parse HEAD)"
CHANGED_FILES="$(git diff --name-only "$START_SHA" "$END_SHA" 2>/dev/null | wc -l || echo 0)"

cat > /output/run-result.json <<RESULTJSON
{
  "status": "$([ $CLAUDE_EXIT -eq 0 ] && echo "success" || echo "error")",
  "exit_code": ${CLAUDE_EXIT},
  "start_sha": "${START_SHA}",
  "end_sha": "${END_SHA}",
  "branch": "${BRANCH_NAME}",
  "files_changed": ${CHANGED_FILES},
  "finished_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
}
RESULTJSON

log ""
log "═══════════════════════════════════════════════════════"
log " Done! ${CHANGED_FILES} file(s) changed"
log " Branch: ${BRANCH_NAME}"
log " Diffs saved to /output/diffs/"
log "═══════════════════════════════════════════════════════"
