# NEWSHD — News Portal

A full-stack news portal with public reading experience, member accounts, threaded comments,
a login-gated contact form, and a role-based React admin dashboard.

## Tech Stack

| Layer          | Technology                                                            |
|----------------|------------------------------------------------------------------------|
| Backend        | PHP 8.1+ (procedural + helper-function architecture), PDO (MySQLi driver) |
| Database       | MySQL 8 / MariaDB 10.4+ (InnoDB, normalized schema, FULLTEXT search)  |
| Frontend       | HTML5, CSS3 (custom properties for theming), vanilla JS               |
| Interactive UI | React 18 (via CDN, no build step) for live search, comments, admin panel |
| Admin charts   | Chart.js                                                               |
| Security       | PDO prepared statements, `password_hash`/`password_verify`, CSRF tokens, session hardening, rate limiting |

No bundler/build step is required anywhere in this project — React components are written with
`React.createElement` directly and loaded as plain `<script>` tags from CDN.

## Folder Structure

```
newshd/
├── admin/                  Admin panel (PHP shell + React SPA)
│   ├── index.php           Dashboard shell, loads admin/js/app.js
│   ├── login.php           Separate admin login (role-gated)
│   └── js/app.js           React admin dashboard (all CRUD screens)
├── api/                    JSON REST-style endpoints
│   ├── admin/index.php     Admin API: dashboard, articles, categories,
│   │                       users, comments, messages, settings, roles
│   ├── comments.php        Public comment CRUD + voting
│   ├── search.php          Live/instant search
│   ├── bookmarks.php       Save/unsave articles
│   └── newsletter.php      Newsletter signup
├── assets/
│   ├── css/                main.css (public site) + admin.css (admin panel)
│   └── js/                 theme.js, main.js, components/ (live-search, comments)
├── config/
│   ├── config.php          App constants, session hardening, bootstraps includes
│   └── database.php        PDO singleton connection
├── includes/                Shared PHP: auth.php, csrf.php, functions.php,
│                            pagination.php, header.php, footer.php
├── database/schema.sql      Full schema + seed data
├── images/                  Site logo, avatars fallback, background art
├── uploads/                 User-uploaded article/avatar images (write-protected)
├── index.php, article.php, category.php, search.php   Public pages
├── login.php, register.php, logout.php,
│   verify.php, forgot-password.php, profile.php        Auth & account pages
├── contact.php               Login-gated contact form
├── sitemap.php, robots.txt   SEO
└── .htaccess                 Clean URLs, security headers, folder protection
```

## Setup Instructions (XAMPP / WAMP / LAMP)

1. **Copy the project** into your server's web root, e.g. `htdocs/newshd` (XAMPP/WAMP) or
   `/var/www/html/newshd` (LAMP).

2. **Create the database.** Open phpMyAdmin (or the `mysql` CLI) and import the schema:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   This creates the `news_portal` database, all tables with foreign keys and indexes, and
   seeds demo data (roles, users, categories, tags, sample published articles, comments,
   and site settings).

3. **Configure the database connection.** Edit `config/config.php` if your credentials differ
   from the defaults:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'news_portal');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
   Also update `APP_URL` to match wherever you placed the project, e.g.
   `http://localhost/newshd`.

4. **Set folder permissions** (Linux/Mac only) so uploaded images can be written:
   ```bash
   chmod -R 755 uploads
   ```

5. **Enable `mod_rewrite`** on Apache (already on by default in XAMPP/WAMP) so `.htaccess`
   clean URLs and security headers apply.

6. **Visit the site**: `http://localhost/newshd/`

### Demo accounts (all seeded with password `Admin@123456`)

| Role   | Email                | Notes                              |
|--------|-----------------------|-------------------------------------|
| Admin  | admin@newshd.com      | Full access, incl. Settings & Users |
| Editor | editor@newshd.com     | Manage articles, moderate comments, manage users |
| Author | author@newshd.com     | Create/edit own articles            |
| User   | user@newshd.com       | Regular reader — comments, bookmarks, contact form |

Admin/editor/author accounts can sign in at `/admin/login.php` to reach the dashboard.
Regular readers sign in at `/login.php`.

> **Security note:** change these demo passwords (or delete the demo rows) before deploying
> anywhere public.

## Key Features

- **Public site:** breaking-news hero slider, category sections, trending sidebar, full
  article pages with related posts and social share buttons, category & tag browsing,
  debounced live search (React) plus a dedicated search results page, reusable pagination.
- **Accounts:** registration with password-strength validation, simulated email verification,
  password reset flow, profile editing with avatar upload, password change, comment history,
  and saved/bookmarked articles.
- **Comments:** nested replies, edit window, like/dislike voting, guest read-only view with a
  login prompt, moderation queue for staff.
- **Contact Us:** login-gated form routed to the admin inbox with reply/resolve tracking.
- **Admin dashboard (React, role-based: Admin / Editor / Author):** live stats + 7-day activity
  chart, full article CRUD with a lightweight built-in content editor, category CRUD, user role
  & ban management, comment moderation queue, contact message inbox with replies, and
  site-wide settings (Admin only).
- **Security:** PDO prepared statements everywhere, bcrypt password hashing, CSRF tokens on
  every form and API mutation, hardened session cookies, login rate limiting, upload
  type/size validation, uploads folder locked against script execution.
- **SEO:** clean URLs via `.htaccess`, Open Graph tags, dynamic `sitemap.php`, `robots.txt`,
  semantic HTML, lazy-loaded images.
- **Accessibility & theming:** skip-link, ARIA labels on interactive controls, keyboard-usable
  nav, and a persistent dark/light theme toggle (CSS custom properties) applied across the
  entire site, including the admin panel.

## Future Enhancements (intentionally out of scope for this build)

These are acknowledged as valuable but non-essential follow-ups — the system is fully
functional without them:

- Real transactional email delivery (PHPMailer/SMTP) for verification & password reset,
  instead of the current simulated on-page links
- Full WYSIWYG editor (TinyMCE/Quill) in place of the lightweight HTML toolbar
- Scheduled publishing via a cron job that flips `scheduled` articles to `published`
- Server-side spam/profanity filtering for comments and contact messages
- Social login (Google/Facebook OAuth)
- Image optimization/resizing pipeline and CDN delivery for uploads
- Article revision history / autosave drafts
- Push notifications or email digests for breaking news
- Automated test suite (PHPUnit for backend, a JS test runner for components)

## Database Overview

11 normalized InnoDB tables: `roles`, `users`, `categories`, `tags`, `articles`, `article_tags`
(pivot), `comments`, `comment_votes`, `bookmarks`, `contact_messages`, `site_settings`,
`activity_logs`, `login_attempts`, `newsletter_subscribers` — with foreign keys, indexes on all
search/filter columns, and a FULLTEXT index on `articles(title, excerpt, content)`.
See `database/schema.sql` for the complete DDL and seed data, and the accompanying project
report for the entity-relationship diagram.
