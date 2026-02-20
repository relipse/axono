# PostFlow

A self-hosted social media scheduling app built with Laravel. Connect your Twitter, Facebook, and LinkedIn accounts, compose posts, and schedule them with cron-based automation.

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL, SQLite, or PostgreSQL

## Quick Start

```bash
git clone <repo-url> postflow
cd postflow
composer install
npm install && npm run build
```

Then open the app in your browser. On the first visit you'll see a **Setup Wizard** that walks you through:

1. Database configuration (SQLite works out of the box, or enter MySQL/PostgreSQL credentials)
2. Creating the `.env` file and application key
3. Running migrations and seeding subscription plans
4. Creating your admin account

Once setup completes you're taken straight to the dashboard.

## Manual Setup (alternative)

If you prefer to configure everything by hand:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials, then:

```bash
php artisan migrate
php artisan db:seed
```

## Running Locally

```bash
# All-in-one dev server (web + queue worker + Vite + log tail)
composer dev

# Or just the web server
php artisan serve
```

## OAuth Setup (optional)

To enable "Connect with Twitter/Facebook/LinkedIn" buttons, register an app on each platform's developer portal and add the credentials to `.env`:

| Platform | Env Variables | Developer Portal |
|----------|--------------|-----------------|
| Twitter | `TWITTER_CLIENT_ID`, `TWITTER_CLIENT_SECRET` | https://developer.twitter.com |
| Facebook | `FACEBOOK_APP_ID`, `FACEBOOK_APP_SECRET` | https://developers.facebook.com |
| LinkedIn | `LINKEDIN_CLIENT_ID`, `LINKEDIN_CLIENT_SECRET` | https://linkedin.com/developers |

Set each platform's callback URL to: `https://yourdomain.com/auth/{platform}/callback`

## Features

- **Multi-platform posting** — Twitter, Facebook, LinkedIn, Instagram
- **Cron-based scheduling** — Fine-grained control with cron expressions
- **Bulk posting from files** — Upload CSV/text files and schedule line-by-line
- **Subscription tiers** — Starter, Professional, and Enterprise plans
- **Live character-count previews** — Platform-specific limits while composing
- **OAuth integration** — Connect accounts via Laravel Socialite

## Tech Stack

- Laravel 12 / PHP 8.2
- Vanilla CSS design system (no Tailwind dependency)
- Laravel Socialite for OAuth
- Database-backed queues and sessions

## License

MIT
