# R Square Construction website

Company website and admin dashboard for **R Square Construction** (Ontario, Canada), built with Laravel 13, Blade, Livewire, Alpine.js, Tailwind CSS 4 and MySQL.

## Features

**Public site**
- Home, About, Services (+ detail pages), Projects (+ detail pages, filter by category and status), Gallery with lightbox, Why Choose Us, Careers, Contact, Request a Quote.
- Quote requests with optional document upload and a unique reference number; contact form; job applications with resume upload.
- Responsive from 320px phones to wide desktops, scroll animations that respect reduced-motion, SEO tags, Open Graph image, XML sitemap and `robots.txt`.

**Admin dashboard (`/admin`)**
- Inbox: quote requests (status, notes, reply by email), contact messages, job applications (resume download).
- Content: projects (multi-image), services, gallery, job openings, testimonials (approval required), highlights, statistics.
- **Pages:** publish/unpublish any page (hidden pages leave the menu, footer and sitemap and return 404), reorder and relabel the menu, and add custom pages.
- **Website Content:** every heading, paragraph, button label and main image on the site is editable, tab by tab.
- Settings: company details, logo, favicon, social links, SEO. Staff accounts with `admin` / `editor` roles.

**Engineering**
- Submissions are always saved to MySQL first; email failures are logged and never lose data.
- Private uploads (quote documents, resumes) are never web-accessible; images are re-encoded on upload.
- CSRF, rate limiting on public forms and login, role-based policies, security headers, no public registration.

## Local setup

Requires PHP 8.4+, Composer, Node 20+ and MySQL.

```bash
composer install
npm install
cp .env.example .env          # then set DB_* and, for the first admin, ADMIN_EMAIL / ADMIN_PASSWORD
php artisan key:generate
php artisan migrate
php artisan db:seed           # categories, services, highlights, first admin from .env
php artisan db:seed --class=DemoSeeder   # optional: clearly-marked sample projects and gallery images
npm run build                 # or `npm run dev` while developing
php artisan serve
```

Create more staff accounts with `php artisan admin:create`.

## Tests

```bash
php artisan test
```

Feature tests run on in-memory SQLite and cover the public pages, forms and emails, uploads, the admin workflows, permissions and branding.

## Deploying to cPanel with Git

See [DEPLOYMENT.md](DEPLOYMENT.md). The repository contains a `.cpanel.yml` that runs `deploy/cpanel-deploy.sh` on *Deploy HEAD Commit*. `public/build` and `vendor/` are committed on purpose so shared hosting needs neither Node nor Composer; run `npm run build` before committing CSS/JS changes and commit `vendor/` after a `composer update`.

## Content notes

Statistics, testimonials and job openings are never pre-filled: add real ones in the admin. Sample projects and gallery images created by `DemoSeeder` are labelled as demonstration content.
