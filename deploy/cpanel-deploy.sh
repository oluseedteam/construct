#!/usr/bin/env bash
#
# R Square Construction: cPanel Git deployment.
# Run by .cpanel.yml from the repository root (the checkout cPanel keeps under ~/repositories/...).
#
# The repository checkout IS the application. Only public/ is published into public_html,
# so source code, vendor/ and .env are never web-accessible.
#
# Optional per-server overrides live OUTSIDE the repo in ~/.rsquare-deploy.env, e.g.:
#   PUBLIC_PATH=/home/cpaneluser/public_html        # default: $HOME/public_html (use a sub-folder for add-on domains)
#   PHP_BIN=/opt/cpanel/ea-php84/root/usr/bin/php   # default: auto-detected, must be PHP 8.3+
#   COMPOSER_BIN=/opt/cpanel/composer/bin/composer  # default: auto-detected
#
# Safe to re-run. It never touches .env, storage/ data or uploaded files.

set -euo pipefail

say()  { printf '\n==> %s\n' "$*"; }
warn() { printf 'WARNING: %s\n' "$*" >&2; }
die()  { printf '\nERROR: %s\n' "$*" >&2; exit 1; }

APP_PATH="$(pwd)"
[ -f "$APP_PATH/artisan" ] || die "Run this from the repository root (artisan not found in $APP_PATH)."

# shellcheck disable=SC1091
[ -f "$HOME/.rsquare-deploy.env" ] && . "$HOME/.rsquare-deploy.env"
PUBLIC_PATH="${PUBLIC_PATH:-$HOME/public_html}"

say "Deploying $APP_PATH -> $PUBLIC_PATH"

# ---------------------------------------------------------------- PHP (8.3+ required by Laravel 13)
find_php() {
    local candidate
    for candidate in "${PHP_BIN:-}" \
        /opt/cpanel/ea-php85/root/usr/bin/php /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php \
        /usr/local/bin/php "$(command -v php || true)"; do
        [ -n "$candidate" ] && [ -x "$candidate" ] || continue
        if "$candidate" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null; then
            echo "$candidate"
            return 0
        fi
    done
    return 1
}
PHP_BIN="$(find_php)" || die "PHP 8.3 or newer was not found. In cPanel > MultiPHP Manager select PHP 8.3+, or set PHP_BIN in ~/.rsquare-deploy.env."
echo "PHP: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"

# ---------------------------------------------------------------- .env sanity (never created or changed by this script)
[ -f .env ] || die ".env is missing in $APP_PATH. Copy .env.example to .env and fill it in (see DEPLOYMENT.md), then deploy again."
grep -qE '^APP_KEY=base64:.+' .env || die "APP_KEY is empty in .env. Run: $PHP_BIN artisan key:generate"
grep -qE '^APP_DEBUG=true' .env && warn "APP_DEBUG=true in .env. Set APP_DEBUG=false on a live site."

# ---------------------------------------------------------------- Composer dependencies
run_composer() {
    local bin="$1"; shift
    # A phar/PHP script must run with the right PHP; its shebang may point at an older default php.
    if head -n 1 "$bin" 2>/dev/null | grep -q 'php'; then
        "$PHP_BIN" "$bin" "$@"
    else
        "$bin" "$@"
    fi
}
find_composer() {
    local candidate
    for candidate in "${COMPOSER_BIN:-}" /opt/cpanel/composer/bin/composer "$(command -v composer || true)" "$HOME/composer.phar" "$HOME/bin/composer"; do
        [ -n "$candidate" ] && [ -f "$candidate" ] && { echo "$candidate"; return 0; }
    done
    return 1
}

if [ "${SKIP_COMPOSER:-0}" = "1" ]; then
    say "Skipping Composer (SKIP_COMPOSER=1)"
elif COMPOSER="$(find_composer)"; then
    say "Installing PHP dependencies"
    export COMPOSER_HOME="${COMPOSER_HOME:-$HOME/.composer}"
    run_composer "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress
elif [ -f vendor/autoload.php ]; then
    warn "Composer not found; keeping the existing vendor/ folder."
else
    die "Composer not found and vendor/ is missing. Install Composer (cPanel Terminal: https://getcomposer.org) or set COMPOSER_BIN in ~/.rsquare-deploy.env."
fi
[ -f vendor/autoload.php ] || die "vendor/autoload.php is missing after install."

# ---------------------------------------------------------------- writable folders
say "Preparing storage"
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/framework/testing \
         storage/logs storage/app/public storage/app/private bootstrap/cache
chmod -R u+rwX,g+rwX storage bootstrap/cache 2>/dev/null || warn "Could not chmod storage/ and bootstrap/cache/; make sure they are writable."

# ---------------------------------------------------------------- publish public/ into the document root
say "Publishing public/ to $PUBLIC_PATH"
mkdir -p "$PUBLIC_PATH"
[ -f "$APP_PATH/public/build/manifest.json" ] || die "public/build/manifest.json is missing. Run 'npm run build' locally and commit public/build."
rm -rf "$PUBLIC_PATH/build"   # hashed asset names change on every build; stale files would pile up
# Copy everything except uploads (storage), the dev hot file and index.php (generated below).
( cd "$APP_PATH/public" && tar cf - --exclude=./storage --exclude=./hot --exclude=./index.php . ) | ( cd "$PUBLIC_PATH" && tar xf - )
# Front controller that points at this checkout instead of ../
sed "s#__RSQUARE_APP_PATH__#${APP_PATH}#g" "$APP_PATH/deploy/public_html/index.php" > "$PUBLIC_PATH/index.php"

# Uploads: either PUBLIC_DISK_ROOT in .env points inside public_html, or we link public_html/storage -> storage/app/public.
if grep -qE '^PUBLIC_DISK_ROOT=.+' .env; then
    echo "Uploads: PUBLIC_DISK_ROOT is set in .env"
elif [ ! -e "$PUBLIC_PATH/storage" ]; then
    if ln -s "$APP_PATH/storage/app/public" "$PUBLIC_PATH/storage" 2>/dev/null; then
        echo "Uploads: linked $PUBLIC_PATH/storage -> storage/app/public"
    else
        warn "Could not create the storage link. Set PUBLIC_DISK_ROOT and PUBLIC_DISK_URL in .env (see DEPLOYMENT.md) or uploaded images will not show."
    fi
fi

# ---------------------------------------------------------------- database + caches
say "Running database migrations"
"$PHP_BIN" artisan migrate --force

# One-time starter data (categories, services, highlights, first admin from ADMIN_* in .env). Idempotent; never repeated.
if [ ! -f storage/app/.seeded ]; then
    say "First deploy: seeding starter data"
    "$PHP_BIN" artisan db:seed --force
    touch storage/app/.seeded
fi

say "Refreshing caches"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

say "Deploy finished."
