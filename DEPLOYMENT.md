# Deploying R Square Construction to cPanel with Git

The repository checkout on the server **is** the application. On every deploy, `.cpanel.yml` runs
[`deploy/cpanel-deploy.sh`](deploy/cpanel-deploy.sh), which publishes only `public/` into `public_html`.
Source code, `vendor/` and `.env` stay outside the document root.

```
/home/cpaneluser/
├── repositories/rsquare/   ← git checkout = the Laravel app (app, vendor, storage, .env …)
└── public_html/            ← document root: index.php, .htaccess, build/, images/, storage/ (generated on deploy)
```

## 1. One-time server setup

1. **PHP version:** cPanel > *MultiPHP Manager* > select **PHP 8.3 or newer** for the domain.
   Make sure these extensions are on: `pdo_mysql, mbstring, openssl, fileinfo, gd, curl, xml, zip, tokenizer, ctype`.
2. **Database:** cPanel > *MySQL Databases*: create a database and a user, add the user to the database with *All Privileges*.
3. **Clone the repository:** cPanel > *Git Version Control* > *Create*. Paste the clone URL (for a private repo, add the
   cPanel SSH public key as a *deploy key* on GitHub/GitLab) and keep the default path, e.g. `repositories/rsquare`.
4. **Create `.env`** inside that folder (File Manager > `repositories/rsquare` > copy `.env.example` to `.env`) and fill in:
   - `APP_URL=https://your-domain.com`, `APP_DEBUG=false`
   - `APP_KEY`: generate locally with `php artisan key:generate --show` and paste the `base64:...` value
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (cPanel prefixes both with your account name)
   - `MAIL_*` (cPanel > *Email Accounts* > *Connect Devices* shows the SMTP values)
   - `ADMIN_EMAIL` and a **strong** `ADMIN_PASSWORD` (12+ characters). Used once, on the first deploy, to create the first admin.
5. *(Only if your site is an add-on domain or sub-domain)* create `~/.rsquare-deploy.env` with the document root:
   ```
   PUBLIC_PATH=/home/cpaneluser/your-domain.com
   ```
   You can also pin the PHP/Composer binaries here (`PHP_BIN=`, `COMPOSER_BIN=`) if auto-detection picks the wrong ones.
6. *Git Version Control* > **Manage** > *Deploy* tab > **Deploy HEAD Commit**.

The first deploy installs dependencies, runs migrations, seeds starter data once, publishes the site and builds caches.
Then **delete `ADMIN_PASSWORD` from `.env`**, sign in at `https://your-domain.com/admin`, and change your password under your name (top right).

### Prefer phpMyAdmin? Import the database instead of running migrations

Two ready-made files are generated in `database/sql/` (they are git-ignored because they contain the admin account's password hash):

| File | Contains |
| --- | --- |
| `rsquare_construction.sql` | **Use this for the live site.** All 24 tables, the six services, categories, highlights, statistic slots (hidden) and the admin account. No sample content. |
| `rsquare_construction_demo.sql` | The same plus 6 clearly-marked *sample* projects and 8 gallery images. For demos and testing. |

1. cPanel > *phpMyAdmin* > click your (empty) database in the left column > **Import**.
2. *Choose file* > pick the `.sql` file > leave the defaults (format SQL, charset utf-8) > **Go**.
3. Fill in `DB_*` in `.env`, deploy the code, and sign in with the admin account in the file.
4. **Change the admin password immediately** (your name, top right > Profile). The file ships with the placeholder password you chose for development; never leave that on a live site.

The import marks every migration as already run, so `php artisan migrate` (run on each deploy) only applies *future* migrations. Importing again drops and recreates the tables, erasing whatever is in them, so import only once into a new database.

## 2. Everyday workflow

```bash
# locally
npm run build                 # only if you changed CSS/JS/Blade classes: public/build is committed
git add -A && git commit -m "Describe the change" && git push

# cPanel > Git Version Control > Manage
#   Pull or Deploy tab > "Update from Remote", then "Deploy HEAD Commit"
```

`public/build` is committed on purpose: shared hosting has no Node. Rebuild before committing CSS/JS changes.

## 3. What a deploy does

1. Finds PHP 8.3+ and checks `.env` and `APP_KEY` exist (it never creates or edits `.env`).
2. `composer install --no-dev --optimize-autoloader`.
3. Ensures `storage/` and `bootstrap/cache/` exist and are writable.
4. Replaces `public_html/build` and copies the rest of `public/`; generates `public_html/index.php` pointing at the checkout.
   Existing files in `public_html` (e.g. `cgi-bin`, `.well-known`) and your uploads are left alone.
5. Links `public_html/storage` to `storage/app/public` for uploaded images (or uses `PUBLIC_DISK_ROOT` if you set it in `.env`).
6. `php artisan migrate --force`.
7. **First deploy only:** `php artisan db:seed --force` (categories, the six services, highlights, hidden statistic slots, first admin from `.env`).
   A marker file `storage/app/.seeded` stops it running again, so content you delete is never resurrected.
8. `optimize:clear`, then `config:cache`, `route:cache`, `view:cache`.

It is safe to run repeatedly. Do **not** run `DemoSeeder` in production (clearly-marked *sample* projects and gallery images).

## 4. First-run checklist (Admin dashboard)

The site ships with R Square Construction's real details (phone, email, location, services, copy) as defaults. Everything below is editable without touching code.

- **Settings**: company name, tagline, logo (PNG/JPG/WebP; the built-in roof-and-R mark is used until you upload one), email, phone, WhatsApp, address, social links, SEO.
- **Website Content**: tabs for Home, About, Services & Projects, Why Choose Us, Contact & Quote, Careers, Footer and Images. Clear a field to restore its default wording.
- **Services / Projects / Gallery / Highlights / Statistics / Testimonials**: add real content. Statistics stay hidden until you enter real figures and tick *Show*. Only add genuine, approved testimonials.
- **Job Openings**: post roles for the Careers page. With none published, visitors are invited to send a general application.
- **Inbox**: *Quote Requests*, *Messages* and *Job Applications* (resumes download through the dashboard only). New items show a badge in the sidebar.
- **Staff Accounts** (admins only): add editors/admins.
- Send a test message, quote and job application to confirm emails arrive (`storage/logs/laravel.log` shows failures).

## 5. Troubleshooting

| Symptom | Fix |
| --- | --- |
| *Deploy HEAD Commit* is greyed out | The checkout has uncommitted changes, or `.cpanel.yml` is not committed. Don't edit tracked files on the server. `.env` and `storage/` are ignored and safe. |
| `PHP 8.3 or newer was not found` | Select PHP 8.3+ in *MultiPHP Manager*, or set `PHP_BIN` in `~/.rsquare-deploy.env`. |
| `Composer not found` | Install Composer in the cPanel terminal, or set `COMPOSER_BIN`. |
| `.env is missing` / `APP_KEY is empty` | Complete step 4 of the setup. |
| Blank page / HTTP 500 | Check `repositories/rsquare/storage/logs/laravel.log`; confirm `storage/` and `bootstrap/cache/` are writable. |
| Uploaded images don't show | Your host blocks symlinks. In `.env` set `PUBLIC_DISK_ROOT=/home/cpaneluser/public_html/storage` and `PUBLIC_DISK_URL=https://your-domain.com/storage`, then deploy. |
| Emails don't arrive | Check `MAIL_*` in `.env`. Submissions are always saved, so nothing is lost; the dashboard shows whether each notification was sent. |

## 6. Notes

- **Email:** submissions are saved to MySQL first. If SMTP fails the visitor still sees success and the failure is logged.
- **Private files:** quote documents and job-application resumes live in `storage/app/private` (never web-accessible) and download only for signed-in staff.
- **Roles:** `admin` can do everything; `editor` can create/edit content and handle inquiries but cannot delete records or change Settings and accounts.
- **Sitemap & robots** are served dynamically at `/sitemap.xml` and `/robots.txt`.
- **Cron / queues** are not required (`QUEUE_CONNECTION=sync`).
- **No Git on the host?** Build locally (`composer install --no-dev -o` and `npm run build`), upload the project outside `public_html`, copy `public/` into `public_html`, and replace `index.php` with `deploy/public_html/index.php` after setting its `__RSQUARE_APP_PATH__`. Then run the artisan steps from section 3 by hand.
