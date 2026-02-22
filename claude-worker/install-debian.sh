#!/usr/bin/env bash
#
# install-debian.sh — One-command installer for Tears Claude Worker on Debian/Ubuntu.
#
# Installs all system dependencies, sets up the Laravel app, database,
# and walks you through granting admin access.
#
# Usage:
#   sudo ./claude-worker/install-debian.sh
#
# What it installs (only if missing):
#   - PHP 8.2+ with required extensions
#   - Composer
#   - Docker CE
#   - SQLite (default DB)
#   - Node.js (optional, for asset compilation)
#
set -euo pipefail

# ── Colors ───────────────────────────────────────────────────────────────────
BOLD="\033[1m"
BLUE="\033[1;34m"
GREEN="\033[1;32m"
YELLOW="\033[1;33m"
RED="\033[1;31m"
RESET="\033[0m"

log()   { printf "${BLUE}[install]${RESET} %s\n" "$*"; }
ok()    { printf "${GREEN}  ✓${RESET} %s\n" "$*"; }
warn()  { printf "${YELLOW}  !${RESET} %s\n" "$*" >&2; }
fail()  { printf "${RED}  ✗${RESET} %s\n" "$*" >&2; exit 1; }
step()  { printf "\n${BOLD}── %s ──${RESET}\n" "$*"; }

# ── Root check ───────────────────────────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
    fail "This script must be run as root (use sudo)."
fi

# Detect the non-root user who invoked sudo
REAL_USER="${SUDO_USER:-$USER}"
REAL_HOME=$(eval echo "~$REAL_USER")

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

echo ""
printf "${BOLD}Tears Claude Worker — Debian/Ubuntu Installer${RESET}\n"
echo "Project directory: $PROJECT_DIR"
echo ""

# ── Step 1: System packages ─────────────────────────────────────────────────
step "Installing system packages"

export DEBIAN_FRONTEND=noninteractive
apt-get update -qq

# PHP
if command -v php &>/dev/null; then
    PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
    ok "PHP already installed ($PHP_VER)"
else
    log "Installing PHP..."
    apt-get install -y -qq \
        php php-cli php-mbstring php-xml php-curl php-zip \
        php-sqlite3 php-mysql php-pgsql php-tokenizer \
        php-bcmath php-json php-fileinfo php-fpm 2>/dev/null || \
    apt-get install -y -qq \
        php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
        php8.2-sqlite3 php8.2-mysql php8.2-pgsql php8.2-tokenizer \
        php8.2-bcmath php8.2-fileinfo php8.2-fpm 2>/dev/null || \
    apt-get install -y -qq php php-cli php-common php-mbstring php-xml php-curl php-zip php-sqlite3
    ok "PHP installed ($(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;'))"
fi

# SQLite
if command -v sqlite3 &>/dev/null; then
    ok "SQLite already installed"
else
    log "Installing SQLite..."
    apt-get install -y -qq sqlite3
    ok "SQLite installed"
fi

# Git
if command -v git &>/dev/null; then
    ok "Git already installed"
else
    log "Installing Git..."
    apt-get install -y -qq git
    ok "Git installed"
fi

# Curl / unzip (needed by Composer)
apt-get install -y -qq curl unzip &>/dev/null
ok "curl, unzip present"

# ── Step 2: Composer ─────────────────────────────────────────────────────────
step "Installing Composer"

if command -v composer &>/dev/null; then
    ok "Composer already installed ($(composer --version 2>/dev/null | head -1))"
else
    log "Downloading Composer..."
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");' 2>/dev/null || echo '')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

    if [[ -n "$EXPECTED_CHECKSUM" ]]; then
        ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
        if [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]]; then
            rm composer-setup.php
            fail "Composer installer checksum mismatch."
        fi
    fi

    php composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
    rm composer-setup.php
    ok "Composer installed to /usr/local/bin/composer"
fi

# ── Step 3: Docker ───────────────────────────────────────────────────────────
step "Installing Docker"

if command -v docker &>/dev/null; then
    ok "Docker already installed ($(docker --version 2>/dev/null))"
else
    log "Installing Docker CE..."

    # Install prerequisites
    apt-get install -y -qq ca-certificates gnupg lsb-release

    # Add Docker GPG key and repo
    install -m 0755 -d /etc/apt/keyrings
    if [[ ! -f /etc/apt/keyrings/docker.gpg ]]; then
        curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
        chmod a+r /etc/apt/keyrings/docker.gpg
    fi

    # Detect distro (Debian or Ubuntu)
    DISTRO_ID=$(. /etc/os-release && echo "$ID")
    DISTRO_CODENAME=$(. /etc/os-release && echo "$VERSION_CODENAME")

    echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/${DISTRO_ID} ${DISTRO_CODENAME} stable" \
        > /etc/apt/sources.list.d/docker.list

    apt-get update -qq
    apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    ok "Docker CE installed"
fi

# Add user to docker group
if id -nG "$REAL_USER" | grep -qw docker; then
    ok "$REAL_USER is already in the docker group"
else
    log "Adding $REAL_USER to the docker group..."
    usermod -aG docker "$REAL_USER"
    ok "$REAL_USER added to docker group (log out and back in for this to take effect)"
fi

# Start Docker if not running
if systemctl is-active --quiet docker 2>/dev/null; then
    ok "Docker daemon is running"
else
    log "Starting Docker daemon..."
    systemctl enable docker --now 2>/dev/null || true
    ok "Docker started"
fi

# ── Step 4: Laravel app setup ────────────────────────────────────────────────
step "Setting up the Laravel application"

cd "$PROJECT_DIR"

# Set ownership so the real user owns everything
chown -R "$REAL_USER:$REAL_USER" "$PROJECT_DIR"

# Run the rest as the real user
run_as_user() {
    su - "$REAL_USER" -c "cd '$PROJECT_DIR' && $1"
}

# Install Composer dependencies
if [[ ! -d "vendor" ]]; then
    log "Installing Composer dependencies..."
    run_as_user "composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader"
    ok "Dependencies installed"
else
    ok "Composer dependencies already installed"
fi

# Create .env
if [[ ! -f ".env" ]]; then
    log "Creating .env from .env.example..."
    run_as_user "cp .env.example .env"

    # Default to SQLite for easy setup
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
    # Comment out MySQL-specific settings
    sed -i 's/^DB_HOST=/#DB_HOST=/' .env
    sed -i 's/^DB_PORT=/#DB_PORT=/' .env
    sed -i 's/^DB_DATABASE=social_scheduler/#DB_DATABASE=social_scheduler/' .env
    sed -i 's/^DB_USERNAME=/#DB_USERNAME=/' .env
    sed -i 's/^DB_PASSWORD=/#DB_PASSWORD=/' .env

    run_as_user "php artisan key:generate --no-interaction"
    ok ".env created with SQLite and a fresh app key"
else
    ok ".env already exists"
fi

# Create SQLite database
DB_PATH="$PROJECT_DIR/database/database.sqlite"
if [[ ! -f "$DB_PATH" ]]; then
    log "Creating SQLite database..."
    touch "$DB_PATH"
    chown "$REAL_USER:$REAL_USER" "$DB_PATH"
    ok "Database created at $DB_PATH"
fi

# Run migrations
log "Running database migrations..."
run_as_user "php artisan migrate --no-interaction --force" 2>/dev/null && \
    ok "Migrations complete" || \
    warn "Some migrations may have already run. Run 'php artisan migrate' to check."

# Mark as installed (bypass setup wizard)
touch "$PROJECT_DIR/storage/installed"

# Set permissions
log "Setting file permissions..."
chmod -R 775 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
chown -R "$REAL_USER:www-data" "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache" 2>/dev/null || \
chown -R "$REAL_USER:$REAL_USER" "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
ok "Permissions set"

# ── Step 5: Make scripts executable ──────────────────────────────────────────
step "Making Claude Worker scripts executable"

chmod +x "$SCRIPT_DIR/claude-worker" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/claude-manager" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/claude-worker-gui" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/scripts/entrypoint.sh" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/scripts/save-diffs.sh" 2>/dev/null || true
mkdir -p "$SCRIPT_DIR/output"
chown "$REAL_USER:$REAL_USER" "$SCRIPT_DIR/output"
ok "Scripts are executable"

# ── Step 6: Set up web server (optional) ─────────────────────────────────────
step "Web server setup"

WEBSERVER=""
if command -v apache2 &>/dev/null; then
    WEBSERVER="apache"
    ok "Apache detected"
elif command -v nginx &>/dev/null; then
    WEBSERVER="nginx"
    ok "Nginx detected"
else
    warn "No web server detected (Apache or Nginx)."
    warn "You can install one with: apt install apache2  OR  apt install nginx"
    warn "Or just use the built-in dev server: php artisan serve --host=0.0.0.0 --port=8000"
fi

if [[ "$WEBSERVER" == "apache" ]]; then
    log "Configuring Apache..."

    a2enmod rewrite headers &>/dev/null || true

    VHOST="/etc/apache2/sites-available/claude-worker.conf"
    if [[ ! -f "$VHOST" ]]; then
        cat > "$VHOST" <<APACHEEOF
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot $PROJECT_DIR/public

    <Directory $PROJECT_DIR/public>
        AllowOverride All
        Require all granted
    </Directory>

    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=\$1

    ErrorLog \${APACHE_LOG_DIR}/claude-worker-error.log
    CustomLog \${APACHE_LOG_DIR}/claude-worker-access.log combined
</VirtualHost>
APACHEEOF
        a2ensite claude-worker.conf &>/dev/null || true
        a2dissite 000-default.conf &>/dev/null || true
        systemctl reload apache2 2>/dev/null || true
        ok "Apache vhost created and enabled"
    else
        ok "Apache vhost already exists at $VHOST"
    fi
fi

if [[ "$WEBSERVER" == "nginx" ]]; then
    log "Configuring Nginx..."

    VHOST="/etc/nginx/sites-available/claude-worker"
    if [[ ! -f "$VHOST" ]]; then
        cat > "$VHOST" <<NGINXEOF
server {
    listen 80;
    server_name _;
    root $PROJECT_DIR/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINXEOF
        ln -sf "$VHOST" /etc/nginx/sites-enabled/claude-worker
        rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true
        systemctl reload nginx 2>/dev/null || true
        ok "Nginx config created and enabled"
    else
        ok "Nginx config already exists at $VHOST"
    fi
fi

# ── Done ─────────────────────────────────────────────────────────────────────
echo ""
printf "${GREEN}${BOLD}════════════════════════════════════════════════════════════${RESET}\n"
printf "${GREEN}${BOLD}  Installation complete!${RESET}\n"
printf "${GREEN}${BOLD}════════════════════════════════════════════════════════════${RESET}\n"
echo ""

# Generate a random admin password if not set
if ! grep -q 'CLAUDE_WORKER_PASSWORD=' .env 2>/dev/null || grep -q 'CLAUDE_WORKER_PASSWORD=$' .env 2>/dev/null; then
    CW_PASS=$(head -c 16 /dev/urandom | base64 | tr -dc 'a-zA-Z0-9' | head -c 16)
    echo "CLAUDE_WORKER_PASSWORD=$CW_PASS" >> .env
    ok "Generated admin password: $CW_PASS"
else
    CW_PASS=$(grep 'CLAUDE_WORKER_PASSWORD=' .env | cut -d= -f2-)
    ok "Admin password already set in .env"
fi

log "Next steps:"
echo ""
echo "  1. Start the server:"
echo ""

if [[ "$WEBSERVER" == "apache" ]]; then
    echo "     # Apache is already configured — visit http://your-server/claude-worker"
    echo ""
    echo "     # Or use the dev server on a specific port:"
fi
if [[ "$WEBSERVER" == "nginx" ]]; then
    echo "     # Nginx is already configured — visit http://your-server/claude-worker"
    echo ""
    echo "     # Or use the dev server on a specific port:"
fi

echo "     cd $PROJECT_DIR"
echo "     php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "  2. Open the admin panel:"
echo ""
echo "     http://your-server:8000/claude-worker/features"
echo ""
echo "     Login with password: $CW_PASS"
echo "     (or change it: php artisan claude-worker:password new-password)"
echo ""

if ! id -nG "$REAL_USER" | grep -qw docker; then
    warn "IMPORTANT: Log out and back in for Docker group access to take effect."
    echo ""
fi

warn "For production, edit .env and set:"
echo "     APP_ENV=production"
echo "     APP_DEBUG=false"
echo "     APP_URL=http://your-domain.com"
echo ""
