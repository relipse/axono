#!/usr/bin/env bash
#
# save-diffs.sh — Generate diff files for easy review.
# Called from entrypoint.sh after Claude Code finishes.
#
set -euo pipefail

START_SHA="${1:-}"
OUTPUT="/output/diffs"

if [[ -z "$START_SHA" ]]; then
    echo "Usage: save-diffs.sh <start-sha>" >&2
    exit 1
fi

mkdir -p "$OUTPUT"

END_SHA="$(git rev-parse HEAD)"

# If no changes, write empty files and exit
if [[ "$START_SHA" == "$END_SHA" ]] && [[ -z "$(git status --porcelain)" ]]; then
    echo "No changes detected." > "${OUTPUT}/summary.txt"
    touch "${OUTPUT}/full.patch"
    touch "${OUTPUT}/stat.txt"
    exit 0
fi

# ── Full unified diff (patch format) ─────────────────────────────────────────
# Includes both committed and uncommitted changes
{
    git diff "$START_SHA" HEAD 2>/dev/null || true
    git diff HEAD 2>/dev/null || true
} > "${OUTPUT}/full.patch"

# ── Diffstat (quick overview of what changed) ────────────────────────────────
{
    git diff --stat "$START_SHA" HEAD 2>/dev/null || true
    if [[ -n "$(git status --porcelain)" ]]; then
        echo ""
        echo "Uncommitted changes:"
        git diff --stat HEAD 2>/dev/null || true
    fi
} > "${OUTPUT}/stat.txt"

# ── Per-file diffs (one patch file per changed file) ─────────────────────────
PER_FILE_DIR="${OUTPUT}/per-file"
mkdir -p "$PER_FILE_DIR"

changed_files="$(git diff --name-only "$START_SHA" HEAD 2>/dev/null || true)"
if [[ -n "$changed_files" ]]; then
    while IFS= read -r file; do
        if [[ -n "$file" ]]; then
            # Replace path separators with double-underscores for flat filenames
            safe_name="$(echo "$file" | tr '/' '__')"
            git diff "$START_SHA" HEAD -- "$file" > "${PER_FILE_DIR}/${safe_name}.patch" 2>/dev/null || true
        fi
    done <<< "$changed_files"
fi

# ── Human-readable summary ───────────────────────────────────────────────────
{
    echo "Tears Claude Worker — Diff Summary"
    echo "════════════════════════════════════════════════════════"
    echo ""
    echo "Base commit:   ${START_SHA}"
    echo "Final commit:  ${END_SHA}"
    echo ""
    echo "── Files changed ──────────────────────────────────────"
    git diff --stat "$START_SHA" HEAD 2>/dev/null || echo "(none)"
    echo ""
    echo "── Changed file list ────────────────────────────────────"
    git diff --name-status "$START_SHA" HEAD 2>/dev/null || echo "(none)"
    echo ""
    echo "── New files ────────────────────────────────────────────"
    git diff --name-only --diff-filter=A "$START_SHA" HEAD 2>/dev/null || echo "(none)"
    echo ""
    echo "── Modified files ───────────────────────────────────────"
    git diff --name-only --diff-filter=M "$START_SHA" HEAD 2>/dev/null || echo "(none)"
    echo ""
    echo "── Deleted files ────────────────────────────────────────"
    git diff --name-only --diff-filter=D "$START_SHA" HEAD 2>/dev/null || echo "(none)"
    echo ""
    echo "════════════════════════════════════════════════════════"
    echo "Per-file patches: /output/diffs/per-file/"
    echo "Full patch:       /output/diffs/full.patch"
} > "${OUTPUT}/summary.txt"

echo "Diffs saved to ${OUTPUT}/"
