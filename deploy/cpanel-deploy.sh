#!/usr/bin/env bash
#
# R Square Construction: cPanel Git deployment. Fast and terminal-free.
# cPanel runs this from .cpanel.yml when you click "Deploy HEAD Commit" in Git Version Control.
#
# The repository checkout IS the application and it already contains vendor/ (committed on purpose),
# so a deploy needs NO Composer and normally finishes in seconds:
#   publish public/ -> public_html, run database migrations, seed starter data once, rebuild caches.
# Only public/ is published, so source code, vendor/ and .env are never web-accessible.
#
# Everything is logged to ~/rsquare-deploy.log (open it in cPanel > File Manager, or `tail -f` it in Terminal).
#
# Optional per-server overrides live OUTSIDE the repo in ~/.rsquare-deploy.env, e.g.:
#   PUBLIC_PATH=/home/cpaneluser/public_html        # default: $HOME/public_html (use a sub-folder for add-on domains)
#   PHP_BIN=/opt/cpanel/ea-php84/root/usr/bin/php   # default: auto-detected (newest PHP that satisfies vendor/)
#   RUN_COMPOSER=1                                  # force a `composer install` even though vendor/ is committed
#
# Safe to re-run. It never touches .env, storage/ data or uploaded files.

set -euo pipefail

LOG="$HOME/rsquare-deploy.log"
START=$(date +%s)

say()  { printf '\n==> %s\n' "$*"; }
warn() { printf 'WARNING: %s\n' "$*" >&2; }
die()  { printf '\nERROR: %s\nSee %s for details.\n' "$*" "$LOG" >&2; exit 1; }

deploy() {
    [ -f artisan ] || die "Run this from the repository root (artisan not found in $(pwd))."

    # shellcheck disable=SC1091
    [ -f "$HOME/.rsquare-deploy.env" ] && . "$HOME/.rsquare-deploy.env"
    PUBLIC_PATH="${PUBLIC_PATH:-$HOME/public_html}"

    # ------------------------------------------------------------ PHP: newest cPanel build that passes vendor's platform check
    php_ok() {
        [ -n "$1" ] && [ -x "$1" ] || return 1
        if [ -f vendor/composer/platform_check.php ]; then
            "$1" -d display_errors=0 -r 'require "vendor/composer/platform_check.php";' >/dev/null 2>&1
        else
            "$1" -r 'exit(version_compare(PHP_VERSION, "8.4.0", ">=") ? 0 : 1);' 2>/dev/null
        fi
    }
    PHP_BIN="${PHP_BIN:-}"
    if ! php_ok "$PHP_BIN"; then
        PHP_BIN=""
        for candidate in /opt/cpanel/ea-php86/root/usr/bin/php /opt/cpanel/ea-php85/root/usr/bin/php /opt/cpanel/ea-php84/root/usr/bin/php \
            /usr/local/bin/php "$(command -v php || true)"; do
            if php_ok "$candidate"; then PHP_BIN="$candidate"; break; fi
        done
    fi
    [ -n "$PHP_BIN" ] || die "No suitable PHP found. This application needs PHP 8.4 or newer: in cPanel > MultiPHP Manager select PHP 8.4+ for this domain (or set PHP_BIN in ~/.rsquare-deploy.env), then deploy again."
    echo "PHP: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"
    for ext in pdo_mysql mbstring openssl fileinfo gd curl xml zip tokenizer ctype; do
        "$PHP_BIN" -m 2>/dev/null | grep -qi "^${ext}$" || warn "PHP extension '$ext' is not enabled (cPanel > Select PHP Version > Extensions)."
    done

    # Resolve the checkout path with PHP so the generated index.php gets exactly the path PHP itself can open.
    APP_PATH="$("$PHP_BIN" -r 'echo str_replace("\\", "/", realpath("."));')"
    say "Deploying $APP_PATH -> $PUBLIC_PATH"

    # ------------------------------------------------------------ .env sanity (never created or changed by this script)
    [ -f .env ] || die ".env is missing in $APP_PATH. In File Manager copy .env.example to .env and fill it in (see DEPLOYMENT.md), then deploy again."
    grep -qE '^APP_KEY=base64:.+' .env || die "APP_KEY is empty in .env. Generate one with 'php artisan key:generate' (Terminal) or paste one generated on your PC."
    grep -qE '^APP_DEBUG=true' .env && warn "APP_DEBUG=true in .env. Set APP_DEBUG=false on a live site."

    # ------------------------------------------------------------ dependencies: already in the repository
    if [ -f vendor/autoload.php ] && [ "${RUN_COMPOSER:-0}" != "1" ]; then
        say "Dependencies: using the committed vendor/ folder (no Composer needed)"
    else
        say "Dependencies: running Composer (RUN_COMPOSER=1 or vendor/ missing)"
        export COMPOSER_HOME="${COMPOSER_HOME:-$HOME/.composer}" COMPOSER_MEMORY_LIMIT=-1 COMPOSER_ALLOW_SUPERUSER=1
        COMPOSER=""
        for candidate in "${COMPOSER_BIN:-}" /opt/cpanel/composer/bin/composer "$(command -v composer || true)" "$HOME/bin/composer.phar"; do
            [ -n "$candidate" ] && [ -f "$candidate" ] && { COMPOSER="$candidate"; break; }
        done
        if [ -z "$COMPOSER" ]; then
            echo "Composer not found: downloading it to ~/bin (one-time)"
            mkdir -p "$HOME/bin"
            "$PHP_BIN" -r "copy('https://getcomposer.org/installer', '$HOME/bin/composer-setup.php');" || die "Could not download Composer (no outbound internet?)."
            expected="$("$PHP_BIN" -r "echo trim(file_get_contents('https://composer.github.io/installer.sig'));")"
            actual="$("$PHP_BIN" -r "echo hash_file('sha384', '$HOME/bin/composer-setup.php');")"
            [ "$expected" = "$actual" ] || die "Composer installer checksum mismatch; download aborted."
            "$PHP_BIN" "$HOME/bin/composer-setup.php" --quiet --install-dir="$HOME/bin" --filename=composer.phar || die "Composer setup failed."
            rm -f "$HOME/bin/composer-setup.php"
            COMPOSER="$HOME/bin/composer.phar"
        fi
        if head -c 64 "$COMPOSER" | grep -q php; then
            "$PHP_BIN" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist || die "composer install failed."
        else
            "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist || die "composer install failed."
        fi
    fi
    [ -f vendor/autoload.php ] || die "vendor/autoload.php is missing."

    # ------------------------------------------------------------ writable folders
    say "Preparing storage"
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/framework/testing \
             storage/logs storage/app/public storage/app/private bootstrap/cache
    chmod -R u+rwX,g+rwX storage bootstrap/cache 2>/dev/null || warn "Could not chmod storage/ and bootstrap/cache/; make sure they are writable."

    # ------------------------------------------------------------ publish public/ into the document root
    say "Publishing public/ to $PUBLIC_PATH"
    mkdir -p "$PUBLIC_PATH"
    [ -f "$APP_PATH/public/build/manifest.json" ] || die "public/build/manifest.json is missing. Run 'npm run build' locally and commit public/build."
    rm -rf "$PUBLIC_PATH/build"   # hashed asset names change on every build; stale files would pile up
    # Copy everything except uploads (storage), the dev hot file and index.php (generated below).
    ( cd "$APP_PATH/public" && tar cf - --exclude=./storage --exclude=./hot --exclude=./index.php . ) | ( cd "$PUBLIC_PATH" && tar xf - )
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

    # ------------------------------------------------------------ database + caches
    say "Running database migrations"
    "$PHP_BIN" artisan migrate --force --no-interaction || die "Database migration failed. Check DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env and that the user has ALL PRIVILEGES on the database."

    # One-time starter data (categories, services, highlights, pages, first admin from ADMIN_* in .env). Idempotent; never repeated.
    if [ ! -f storage/app/.seeded ]; then
        say "First deploy: seeding starter data"
        "$PHP_BIN" artisan db:seed --force --no-interaction
        touch storage/app/.seeded
    fi

    say "Refreshing caches"
    "$PHP_BIN" artisan optimize:clear --no-interaction
    "$PHP_BIN" artisan package:discover --no-interaction
    "$PHP_BIN" artisan config:cache --no-interaction
    "$PHP_BIN" artisan route:cache --no-interaction
    "$PHP_BIN" artisan view:cache --no-interaction

    say "Deploy finished successfully in $(( $(date +%s) - START ))s."
}

{ printf '\n===== Deploy started %s =====\n' "$(date '+%Y-%m-%d %H:%M:%S')"; } >> "$LOG" 2>/dev/null || LOG=/dev/null
# Run with output copied to the log. PIPESTATUS keeps the deploy's own exit code (not tee's).
deploy 2>&1 | tee -a "$LOG"
exit "${PIPESTATUS[0]}"
