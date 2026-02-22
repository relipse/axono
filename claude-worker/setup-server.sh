#!/usr/bin/env bash
#
# setup-server.sh — Set up the Tears Claude Worker web admin on a server.
#
# This script:
#   1. Checks prerequisites (PHP, Composer, Docker, Node)
#   2. Installs Laravel dependencies
#   3. Configures the .env file
#   4. Runs database migrations
#   5. Makes the claude-worker scripts executable
#   6. Optionally starts the Laravel dev server
#
# Usage:
#   cd /path/to/axono
#   ./claude-worker/setup-server.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

# ── Colors ───────────────────────────────────────────────────────────────────
BOLD="\033[1m"
BLUE="\033[1;34m"
GREEN="\033[1;32m"
YELLOW="\033[1;33m"
RED="\033[1;31m"
RESET="\033[0m"

log()   { printf "${BLUE}[setup]${RESET} %s\n" "$*"; }
ok()    { printf "${GREEN}[setup]${RESET} %s\n" "$*"; }
warn()  { printf "${YELLOW}[setup]${RESET} %s\n" "$*" >&2; }
error() { printf "${RED}[setup]${RESET} %s\n" "$*" >&2; exit 1; }

# ── Check prerequisites ─────────────────────────────────────────────────────
log "Checking prerequisites..."

check_cmd() {
    if command -v "$1" &>/dev/null; then
        ok "  $1 found: $(command -v "$1")"
        return 0
    else
        warn "  $1 NOT found"
        return 1
    fi
}

MISSING=0
check_cmd php      || MISSING=1
check_cmd composer || MISSING=1
check_cmd docker   || MISSING=1
check_cmd node     || { warn "  node not found (optional — needed only for asset compilation)"; }
check_cmd npm      || { warn "  npm not found (optional — needed only for asset compilation)"; }

if [[ "$MISSING" -eq 1 ]]; then
    echo ""
    error "Missing required dependencies. Install them and re-run this script."
fi

# Check PHP version
PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
log "  PHP version: $PHP_VER"
if [[ "$(php -r 'echo (PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 2) ? 1 : 0;')" != "1" ]]; then
    error "PHP 8.2+ is required. Found: $PHP_VER"
fi

# Check Docker is running
if ! docker info &>/dev/null 2>&1; then
    warn "Docker daemon is not running. Start Docker before launching worker tasks."
fi

# ── Project setup ────────────────────────────────────────────────────────────
cd "$PROJECT_DIR"
log "Project directory: $PROJECT_DIR"

# Install PHP dependencies
if [[ ! -d "vendor" ]]; then
    log "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
else
    log "Composer dependencies already installed."
fi

# Create .env if it doesn't exist
if [[ ! -f ".env" ]]; then
    if [[ -f ".env.example" ]]; then
        log "Creating .env from .env.example..."
        cp .env.example .env
        php artisan key:generate --no-interaction
        ok ".env created with a fresh application key."
    else
        error ".env.example not found. Cannot create .env file."
    fi
else
    log ".env file already exists."
fi

# Set up SQLite database if DATABASE_URL isn't configured for another DB
if grep -q 'DB_CONNECTION=sqlite' .env 2>/dev/null || ! grep -q 'DB_CONNECTION=' .env 2>/dev/null; then
    DB_PATH="$PROJECT_DIR/database/database.sqlite"
    if [[ ! -f "$DB_PATH" ]]; then
        log "Creating SQLite database..."
        touch "$DB_PATH"
        # Update .env to use SQLite
        if grep -q 'DB_CONNECTION=' .env; then
            sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
        else
            echo "DB_CONNECTION=sqlite" >> .env
        fi
    fi
fi

# Run migrations
log "Running database migrations..."
php artisan migrate --no-interaction --force 2>/dev/null || {
    warn "Migrations may have already run or DB needs configuration."
    warn "Run 'php artisan migrate' manually if needed."
}

# ── Make scripts executable ──────────────────────────────────────────────────
log "Making claude-worker scripts executable..."
chmod +x "$SCRIPT_DIR/claude-worker" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/claude-manager" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/claude-worker-gui" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/scripts/entrypoint.sh" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/scripts/save-diffs.sh" 2>/dev/null || true

# Create output directory
mkdir -p "$SCRIPT_DIR/output"

# ── Summary ──────────────────────────────────────────────────────────────────
echo ""
ok "════════════════════════════════════════════════════════════"
ok " Setup complete!"
ok "════════════════════════════════════════════════════════════"
echo ""
log "To start the web admin:"
echo ""
echo "  # Option 1: Laravel development server"
echo "  cd $PROJECT_DIR"
echo "  php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "  # Option 2: Production with Apache/Nginx"
echo "  # Point your web root to: $PROJECT_DIR/public"
echo ""
echo "  # Option 3: Quick start (dev server + queue)"
echo "  cd $PROJECT_DIR"
echo "  composer dev"
echo ""
log "Then open: http://your-server:8000/claude-worker"
echo ""
log "Make sure to:"
echo "  1. Register/login at http://your-server:8000/register"
echo "  2. Navigate to Claude Worker in the nav bar"
echo "  3. Enter your Anthropic API key in the form"
echo "  4. Launch a task!"
echo ""
warn "For production, configure a proper web server (Nginx/Apache)"
warn "and set APP_ENV=production and APP_DEBUG=false in .env"
echo ""
