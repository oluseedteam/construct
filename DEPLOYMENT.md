# Deploying R Square Construction to cPanel with Git (no terminal needed)

Everything on the server happens through the cPanel web interface. You never need SSH or the Terminal feature:
**Git Version Control** pulls the code and runs [`deploy/cpanel-deploy.sh`](deploy/cpanel-deploy.sh) (via `.cpanel.yml`)
when you click **Deploy HEAD Commit**. The repository already contains the `vendor/` folder, so the deploy needs **no
Composer** and normally finishes in **seconds**: it publishes the site, runs database migrations, seeds the starter data
once and rebuilds caches.

```
/home/cpaneluser/
├── repositories/rsquare/   ← git checkout = the Laravel app (app, vendor, storage, .env …)  NOT web-accessible
├── public_html/            ← document root: index.php, .htaccess, build/, images/, storage/  (generated on deploy)
└── rsquare-deploy.log      ← what the last deploys did (open in File Manager if something goes wrong)
```

## 1. One-time setup (about 15 minutes, all in cPanel)

1. **PHP version** — cPanel > *MultiPHP Manager* (or *Select PHP Version*): choose **PHP 8.4 or newer** for your domain (the dependencies require 8.4.1+).
   Under PHP extensions make sure these are ticked: `pdo_mysql, mbstring, openssl, fileinfo, gd, curl, xml, zip, tokenizer, ctype`.
2. **Database** — cPanel > *MySQL Databases*: create a database, create a user, then *Add User To Database* with **All Privileges**.
   Note the full names (cPanel prefixes them, e.g. `cpaneluser_rsquare`).
3. **Clone the repository** — cPanel > *Git Version Control* > *Create*:
   - Clone URL: `https://github.com/oluseedteam/construct.git`
   - Repository Path: `repositories/rsquare` (the default is fine)
   - Leave *Clone a Repository* on, click *Create*.
4. **Create `.env`** — cPanel > *File Manager* > open `repositories/rsquare`:
   - Right-click `.env.example` > *Copy* > name it `.env` (turn on *Show Hidden Files* in File Manager settings to see it).
   - Right-click `.env` > *Edit* and fill in:
     ```
     APP_URL=https://your-domain.com
     APP_DEBUG=false
     APP_KEY=base64:...        ← generate on your PC: php artisan key:generate --show
     DB_DATABASE=cpaneluser_rsquare
     DB_USERNAME=cpaneluser_rsquare
     DB_PASSWORD=...
     MAIL_HOST / MAIL_USERNAME / MAIL_PASSWORD   ← cPanel > Email Accounts > Connect Devices
     ADMIN_EMAIL=you@your-domain.com
     ADMIN_PASSWORD=a-strong-password-12+     ← used once to create the first admin
     ```
   - *(Only for an add-on domain or sub-domain)* also create `/home/cpaneluser/.rsquare-deploy.env` containing
     `PUBLIC_PATH=/home/cpaneluser/your-domain.com` (the domain's document root).
5. **Deploy** — cPanel > *Git Version Control* > *Manage* (next to the repo) > *Pull or Deploy* tab > **Deploy HEAD Commit**.
   It normally completes in under a minute.
6. Open `https://your-domain.com`. Then sign in at `/admin`, change your password (your name, top right > Profile) and
   delete the `ADMIN_PASSWORD` line from `.env`.

## 1b. Same setup using the cPanel Terminal (faster, if you have it)

```bash
# 0) use PHP 8.3+ for this session (cPanel's default `php` may be older)
export PATH="$(ls -d /opt/cpanel/ea-php8[3-9]/root/usr/bin 2>/dev/null | sort | tail -1):$PATH"
php -v                                   # must show PHP 8.4 or newer

# 1) get the code
mkdir -p ~/repositories && cd ~/repositories
git clone https://github.com/oluseedteam/construct.git rsquare
cd rsquare

# 2) configuration
cp .env.example .env
php artisan key:generate                 # fills APP_KEY in .env
nano .env                                # set APP_URL, APP_DEBUG=false, DB_*, MAIL_*, ADMIN_EMAIL, ADMIN_PASSWORD  (Ctrl+O, Enter, Ctrl+X)

# 2b) only for an add-on domain / sub-domain: tell the deploy where its document root is
echo 'PUBLIC_PATH=/home/'"$USER"'/your-domain.com' > ~/.rsquare-deploy.env

# 3) deploy (installs dependencies, publishes public_html, migrates, seeds once, caches)
bash deploy/cpanel-deploy.sh
```

Then open the site, sign in at `/admin`, change your password and remove the `ADMIN_PASSWORD` line from `.env`.

Publishing later versions from the terminal:

```bash
cd ~/repositories/rsquare && git pull && bash deploy/cpanel-deploy.sh
```

(or keep using *Update from Remote* + *Deploy HEAD Commit* in Git Version Control; both run the same script.)

Handy commands:

```bash
tail -50 ~/rsquare-deploy.log                       # what the last deploy did
tail -50 storage/logs/laravel.log                   # application errors
php artisan admin:create someone@example.com        # add another staff account
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 2. Publishing changes afterwards

```bash
# on your PC
npm run build                 # only if CSS/JS or Tailwind classes changed (public/build is committed on purpose)
git add -A && git commit -m "Describe the change" && git push
```

Then in cPanel > *Git Version Control* > *Manage* > *Pull or Deploy* tab:
1. **Update from Remote** (pulls the new commit from GitHub)
2. **Deploy HEAD Commit** (publishes it: dependencies, files, migrations, caches)

That's it. Uploads, `.env` and the database are never touched by a deploy.

## 3. What a deploy does (deploy/cpanel-deploy.sh)

1. Logs everything to `~/rsquare-deploy.log`.
2. Picks the newest cPanel PHP build (`/opt/cpanel/ea-php8x`) that satisfies `vendor/`'s platform check (PHP 8.4+) and checks `.env` and `APP_KEY` exist.
3. Uses the committed `vendor/` folder: **no Composer, no downloads**. (`RUN_COMPOSER=1` in `~/.rsquare-deploy.env` forces a `composer install`, downloading Composer if needed.)
4. Creates the `storage/` folders and makes them writable.
5. Replaces `public_html/build`, copies the rest of `public/`, generates `public_html/index.php` pointing at the checkout.
   Other files in `public_html` (e.g. `cgi-bin`, `.well-known`) and uploaded images are left alone.
6. Links `public_html/storage` to `storage/app/public` for uploads (or uses `PUBLIC_DISK_ROOT` from `.env`).
7. `php artisan migrate --force`.
8. **First deploy only**: `php artisan db:seed --force` (categories, services, highlights, pages, hidden statistic slots,
   first admin from `.env`). A marker file `storage/app/.seeded` stops it running again.
9. `optimize:clear`, then `config:cache`, `route:cache`, `view:cache`.

It is safe to run repeatedly. Do **not** import `DemoSeeder` content on the live site (it adds clearly-marked *sample* projects).

## 4. If a deploy fails

cPanel shows a red "deployment failed" notice. Open **File Manager** > `rsquare-deploy.log` (home folder) and look at the
last `ERROR:` line. The script stops with a plain-English message for each common cause:

| Message / symptom | Fix |
| --- | --- |
| *Deploy HEAD Commit* is greyed out | The server checkout has uncommitted changes. Never edit tracked files on the server (`.env`, `storage/` and `rsquare-deploy.log` are ignored and safe). Use *Update from Remote* first. |
| `No suitable PHP found` | Select PHP 8.4+ in *MultiPHP Manager*, or add `PHP_BIN=/opt/cpanel/ea-php84/root/usr/bin/php` to `~/.rsquare-deploy.env`. |
| `PHP extension 'xyz' is not enabled` (warning) | Tick it in *Select PHP Version* > Extensions. |
| `.env is missing` / `APP_KEY is empty` | Finish step 4 above. |
| Deploy stays "in progress" for many minutes | Run `tail -f ~/rsquare-deploy.log` in Terminal to see the current step. Deploys made before `vendor/` was committed ran Composer and could take 10+ minutes; if one is stuck, `pkill -u $USER -f composer`, then *Update from Remote* and deploy again. |
| `Database migration failed` | Check `DB_*` in `.env`; the user needs *All Privileges* on the database. |
| Blank page / HTTP 500 after deploy | Open `repositories/rsquare/storage/logs/laravel.log`. Usually a wrong `.env` value. |
| Uploaded images don't show | The host blocks symlinks: add `PUBLIC_DISK_ROOT=/home/cpaneluser/public_html/storage` and `PUBLIC_DISK_URL=https://your-domain.com/storage` to `.env`, deploy again. |
| Emails don't arrive | Check `MAIL_*` in `.env`. Submissions are always saved first, nothing is lost; the dashboard shows whether each notification was sent. |

## 5. Alternative: importing the database with phpMyAdmin

Not required (the deploy migrates and seeds for you), but if you prefer, `database/sql/rsquare_construction.sql` creates all
tables with the starter content and the admin account from your local `.env`. Import it into the **empty** database
(phpMyAdmin > database > Import) *before* the first deploy. The deploy then sees every migration as already run.
The file contains the admin password hash, so it is git-ignored; copy it to the server with File Manager if needed.

## 6. First-run checklist (Admin dashboard)

- **Settings**: company name, tagline, logo and site icon, email, phone, WhatsApp, address, social links, SEO.
- **Pages**: every page in menu order. *Unpublish* hides a page from the menu, footer and sitemap (visitors get a 404);
  *Add new page* creates a custom page at `/your-slug`. Built-in pages can be relabelled and reordered but not deleted.
- **Website Content**: tabs for Home, About, Services & Projects, Why Choose Us, Contact & Quote, Careers, Footer and Images.
- **Services / Projects / Gallery / Highlights / Statistics / Testimonials**: add real content. Statistics stay hidden until
  you enter real figures and tick *Show*. Only add genuine, approved testimonials.
- **Job Openings**: post roles for the Careers page. **Inbox**: quotes, messages and job applications.
- **Staff Accounts** (admins only): add editors/admins.
- Send a test message, quote and job application to confirm emails arrive.

## 7. Notes

- Quote documents and resumes are stored in `storage/app/private` (never web-accessible) and download only for signed-in staff.
- Roles: `admin` can do everything; `editor` can create/edit content and handle inquiries but cannot delete records or change Settings and accounts.
- `/sitemap.xml` and `/robots.txt` are generated dynamically. No cron jobs or queues are required.
