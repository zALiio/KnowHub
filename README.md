# KnowHub

![KnowHub knowledge-sharing community](assets/images/knowhub-social.png)

[![PHP syntax](https://github.com/zALiio/KnowHub/actions/workflows/php-lint.yml/badge.svg)](https://github.com/zALiio/KnowHub/actions/workflows/php-lint.yml)

KnowHub is a student knowledge-sharing platform for computer science and AI. It is a college-scale, multi-page application built with HTML, CSS, vanilla JavaScript, Core PHP, PDO, and MySQL.

## Features

- Public question feed, topic pages, search, and student profiles.
- Registration, sign-in, sign-out, profile editing, and session-based authentication.
- Create, edit, and delete your own questions and answers.
- Nine database-backed topics, up to five tags per question, and tag/category/title/body search.
- Question and answer upvotes/downvotes with change and remove behavior.
- Reputation calculated from the current votes: +10 per upvote and −2 per downvote. Scores are derived from vote rows, so changing or removing a vote and deleting content cannot leave a stale stored balance.
- Accepted answers and saved questions.
- Responsive layout, keyboard-visible focus, form feedback, password visibility controls, and confirmation before deleting.

## Requirements

- PHP 8.3 or newer with PDO MySQL, mbstring, and iconv extensions enabled. Use a currently supported PHP branch for a new setup.
- MySQL 8.0.16 or newer, or a recent MariaDB version with InnoDB, foreign keys, and CHECK constraints.
- XAMPP or another local Apache + PHP + MySQL setup.
- A modern browser. The Google Font is optional; system fallbacks are included.

## Install with XAMPP

1. Copy the `KnowHub` folder into `C:\xampp\htdocs\`.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin/`.
4. Select **Import**, choose `KnowHub/database/schema.sql`, and run it. This creates the `knowhub` database and its tables.
5. Import `KnowHub/database/seed.sql` the same way to add topics, sample accounts, questions, answers, votes, and tags.
6. Open `KnowHub/config/config.php`. The defaults are `127.0.0.1`, database `knowhub`, user `root`, and a blank password. Change them if your MySQL setup differs.
7. Visit `http://localhost/KnowHub/`.

`schema.sql` drops and recreates KnowHub's tables. Import it only when setting up a fresh project database or when you intentionally want to reset project data. Do not import it over real data.

### Demo accounts

All four seeded accounts use the password `password`:

- `mariam.h` — `mariam@example.test`
- `omar.codes` — `omar@example.test`
- `nour.data` — `nour@example.test`
- `youssef.dev` — `youssef@example.test`

These accounts and the password are for a local class walkthrough only. The password is intentionally common in the seed data; do not reuse it or expose the demo database publicly. New registrations use PHP's `password_hash()`.

### Local configuration

For a local credential override, copy `config/config.php` to `config/config.local.php` and edit the values there. `config.local.php` is excluded from version control by `.gitignore`. Do not commit real credentials.

## Pages and routes

All page requests use the small front controller at `index.php`:

- `?page=home` — feed, latest/popular/most answered sorting, and pagination.
- `?page=question&id=…` — question details, votes, answers, accepted answer, bookmarking, and owner controls.
- `?page=ask` — create a question.
- `?page=categories` and `?page=category&slug=…` — topic directory and topic feed.
- `?page=search&q=…` — search titles, descriptions, categories, and tags.
- `?page=login` and `?page=register` — authentication.
- `?page=profile&username=…` and `?page=edit-profile` — student profiles.
- `?page=bookmarks` — signed-in user's saved questions.

## Database design

`database/schema.sql` defines the InnoDB tables. `users` own questions and answers; each question belongs to a category. `question_tags` links questions and tags. Separate `question_votes` and `answer_votes` tables enforce one vote per user per target with composite primary keys and foreign keys. `bookmarks` uses the same composite-key pattern. Cascading foreign keys remove dependent votes, tags, bookmarks, and answers with their parent content. The accepted answer uses a nullable foreign key.

Reputation is calculated from live votes, not copied into a user balance or a ledger. This keeps vote changes/removals and content deletion consistent without a second set of bookkeeping writes. The query applies +10 for an upvote and −2 for a downvote.

## Security choices

- PDO prepared statements for database operations.
- `password_hash()` and `password_verify()` for passwords.
- Session ID regeneration after registration and login; HTTP-only and SameSite=Lax session cookies.
- CSRF token checks on every state-changing form.
- Context-appropriate HTML escaping on rendered user content.
- Server-side validation and owner checks for updates/deletes; voters cannot vote on their own content.
- Database unique keys and foreign keys enforce account, vote, tag, and relationship integrity.
- Apache `.htaccess` files deny direct access to configuration, SQL scripts, page templates, and shared includes.

This is an educational local project, not a security certification. Keep Apache's overrides enabled so the `.htaccess` protections apply; on a production host, also configure TLS, backups, error logging, and database privileges.

## Project structure

```text
KnowHub/
├── .github/workflows/         PHP syntax check on push and pull request
├── actions/                   State-changing form handlers
├── assets/css/style.css       Responsive design system and components
├── assets/images/             README banner and repository preview artwork
├── assets/js/app.js           Small browser interactions
├── config/                    Database configuration and PDO connection
├── database/                  Schema and sample data
├── includes/                  Bootstrap, helpers, shared layout, question card
├── pages/                     Page templates
├── index.php                  Routes and page data
└── README.md
```

## Suggested team split

For five students, assign: (1) UI and responsive CSS; (2) authentication and profiles; (3) questions, categories, and tags; (4) answers, votes, and accepted answers; (5) database, integration, QA, and documentation. For six, separate QA/documentation from database integration. Agree on route names and shared helper contracts before parallel edits; merge one feature at a time and run the same local walkthrough after each integration.

## Verification and current limits

GitHub Actions runs `php -l` against every PHP file on pushes and pull requests. This checks PHP syntax; it does not exercise database queries or application flows. PHP, MySQL, and XAMPP were not available in the development workspace, so the application has not yet been verified against a live PHP/MySQL server here. Before presenting, import the schema and seed data locally and walk through registration, question/answer ownership, voting changes/removals, accepted answers, bookmarks, search, and mobile layout.

Current MVP limits: the interface is English-first; no avatar uploads, notifications, admin moderation, or social sign-in. The feed paginates; topic and search results currently show their matching records without separate pagination controls. Font loading can use Google Fonts when online, with local system font fallbacks available.
