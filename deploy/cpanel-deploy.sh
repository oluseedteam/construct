#!/usr/bin/env bash
#
# R Square Construction: cPanel Git deployment. NO TERMINAL NEEDED.
# cPanel runs this from .cpanel.yml when you click "Deploy HEAD Commit" in Git Version Control.
#
# The repository checkout IS the application. Only public/ is published into public_html,
# so source code, vendor/ and .env are never web-accessible.
#
# Everything is logged to ~/rsquare-deploy.log (open it in cPanel > File Manager if a deploy fails).
#
# Optional per-server overrides live OUTSIDE the repo in ~/.rsquare-deploy.env, e.g.:
#   PUBLIC_PATH=/home/cpaneluser/public_html        # default: $HOME/public_html (use a sub-folder for add-on domains)
#   PHP_BIN=/opt/cpanel/ea-php84/root/usr/bin/php   # default: auto-detected, must be PHP 8.3+
#   COMPOSER_BIN=/opt/cpanel/composer/bin/composer  # default: auto-detected, downloaded if missing
#
# Safe to re-run. It never touches .env, storage/ data or uploaded files.

set -euo pipefail

LOG="$HOME/rsquare-deploy.log"
{ printf '\n\n===== Deploy started %s =====\n' "$(date '+%Y-%m-%d %H:%M:%S')"; } >> "$LOG" 2>/dev/null || LOG=/dev/null
exec > >(tee -a "$LOG") 2>&1

say()  { printf '\n==> %s\n' "$*"; }
warn() { printf 'WARNING: %s\n' "$*" >&2; }
die()  { printf '\nERROR: %s\nSee %s for details.\n' "$*" "$LOG" >&2; exit 1; }

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
PHP_BIN="$(find_php)" || die "PHP 8.3 or newer was not found. In cPanel > MultiPHP Manager select PHP 8.3 or newer for this domain, then deploy again."
echo "PHP: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"

for ext in pdo_mysql mbstring openssl fileinfo gd curl xml zip tokenizer ctype; do
    "$PHP_BIN" -m 2>/dev/null | grep -qi "^${ext}$" || warn "PHP extension '$ext' is not enabled (cPanel > Select PHP Version / PHP Extensions)."
done

# ---------------------------------------------------------------- .env sanity (never created or changed by this script)
[ -f .env ] || die ".env is missing in $APP_PATH. In File Manager copy .env.example to .env and fill it in (see DEPLOYMENT.md), then deploy again."
grep -qE '^APP_KEY=base64:.+' .env || die "APP_KEY is empty in .env. Generate one locally with 'php artisan key:generate --show' and paste it into .env."
grep -qE '^APP_DEBUG=true' .env && warn "APP_DEBUG=true in .env. Set APP_DEBUG=false on a live site."

# ---------------------------------------------------------------- Composer (found, or downloaded automatically)
run_composer() {
    local bin="$1"; shift
    # A phar/PHP script must run with the right PHP; its shebang may point at an older default php.
    if head -c 64 "$bin" 2>/dev/null | grep -q 'php'; then
        "$PHP_BIN" "$bin" "$@"
    else
        "$bin" "$@"
    fi
}
find_composer() {
    local candidate
    for candidate in "${COMPOSER_BIN:-}" /opt/cpanel/composer/bin/composer "$(command -v composer || true)" "$HOME/bin/composer.phar" "$HOME/composer.phar"; do
        [ -n "$candidate" ] && [ -f "$candidate" ] && { echo "$candidate"; return 0; }
    done
    return 1
}
download_composer() {
    # Only the final path may go to stdout: the caller captures it with $(...). Messages go to stderr (still logged).
    say "Composer not found on this server: downloading it (one-time)" >&2
    mkdir -p "$HOME/bin"
    local setup="$HOME/bin/composer-setup.php"
    "$PHP_BIN" -r "copy('https://getcomposer.org/installer', '$setup');" || return 1
    local expected actual
    expected="$("$PHP_BIN" -r "echo trim(file_get_contents('https://composer.github.io/installer.sig'));")" || return 1
    actual="$("$PHP_BIN" -r "echo hash_file('sha384', '$setup');")"
    if [ "$expected" != "$actual" ]; then
        rm -f "$setup"
        warn "Composer installer checksum did not match; download aborted."
        return 1
    fi
    "$PHP_BIN" "$setup" --quiet --install-dir="$HOME/bin" --filename=composer.phar || return 1
    rm -f "$setup"
    echo "$HOME/bin/composer.phar"
}

export COMPOSER_HOME="${COMPOSER_HOME:-$HOME/.composer}"
export COMPOSER_MEMORY_LIMIT=-1
export COMPOSER_ALLOW_SUPERUSER=1

if [ "${SKIP_COMPOSER:-0}" = "1" ]; then
    say "Skipping Composer (SKIP_COMPOSER=1)"
else
    COMPOSER="$(find_composer || true)"
    [ -n "$COMPOSER" ] || COMPOSER="$(download_composer || true)"
    if [ -n "$COMPOSER" ]; then
        say "Installing PHP dependencies with $COMPOSER"
        run_composer "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist \
            || die "composer install failed. Check the output above (usually a missing PHP extension or no outbound internet access)."
    elif [ -f vendor/autoload.php ]; then
        warn "Composer could not be found or downloaded; keeping the existing vendor/ folder."
    else
        die "Composer is not available and vendor/ is missing. Ask your host to enable outbound HTTPS (so Composer can be downloaded) or to install Composer, then deploy again."
    fi
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
"$PHP_BIN" artisan migrate --force || die "Database migration failed. Check DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env and that the user has ALL PRIVILEGES on the database."

# One-time starter data (categories, services, highlights, pages, first admin from ADMIN_* in .env). Idempotent; never repeated.
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

say "Deploy finished successfully."
