# TradeLoop

TradeLoop is a Laravel + React/Inertia SaaS demo MVP for small contractors and home improvement businesses.

Product promise:

> Finish the job. TradeLoop handles the follow-up.

The demo includes secure session login, demo login, business profile settings, customers, service types, estimates, invoices, payments, jobs, follow-up templates/rules/messages, simulated SMS/email sending, dashboard metrics, reports, demo data reset, and tests.

## Stack

- Backend: Laravel
- Frontend: React with Inertia.js
- Styling: Tailwind CSS
- Database: MySQL for deployment, SQLite works for local smoke testing
- Auth: Laravel session authentication
- Queue: Laravel database queue
- Scheduler: Laravel Scheduler
- Hosting target: Cloudways
- Messaging: simulated only, no real SMS or email delivery

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan demo:reset
npm run build
php artisan test
```

Demo credentials:

```text
Email: demo@tradeloop.test
Password: password
Business: Smith Home Services
Role: owner
```

Optional seeded staff user:

```text
Email: staff@tradeloop.test
Password: password
Role: staff
```

## Environment

Important values:

```env
APP_NAME=TradeLoop
DB_CONNECTION=mysql
DB_DATABASE=tradeloop
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=queue_jobs
DEMO_MODE=true
MAIL_MAILER=log
SMS_DRIVER=log
SESSION_DRIVER=file
CACHE_STORE=file
```

For the simplest online demo at `https://tradeloop.theaidemocracy.com`, set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tradeloop.theaidemocracy.com
APP_PATH=
ASSET_URL=
SESSION_PATH=/
SESSION_SECURE_COOKIE=true
DEMO_MODE=true
MAIL_MAILER=log
SMS_DRIVER=log
```

Leave `APP_PATH` and `ASSET_URL` blank for the subdomain setup. TradeLoop will run at the domain root of the subdomain.

`APP_PATH` is only an optional fallback for unusual subdirectory installs. It is not needed for `tradeloop.theaidemocracy.com`.

`queue_jobs` is used for Laravel's database queue so TradeLoop can use the `jobs` table for contractor jobs.

Do not commit a real `.env` file or secrets.

## Commands

```bash
php artisan demo:reset
php artisan followups:process-due
php artisan queue:work
php artisan schedule:work
```

In demo mode, `followups:process-due` changes due scheduled follow-ups to `simulated_sent`, sets `sent_at`, and records message events. It never sends real SMS or email.

## Development

```bash
php artisan serve
npm run dev
```

For a production-style local check:

```bash
npm run build
php artisan serve
```

## Cloudways Deployment

Use this as a normal Laravel app on a Cloudways subdomain.

1. Create or point the subdomain to the Cloudways application:

```text
tradeloop.theaidemocracy.com
```

2. Set the application web root to Laravel's `public` folder:

```text
/path/to/tradeloop/public
```

3. Set production demo environment values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tradeloop.theaidemocracy.com
APP_PATH=
ASSET_URL=
SESSION_PATH=/
SESSION_SECURE_COOKIE=true
DEMO_MODE=true
MAIL_MAILER=log
SMS_DRIVER=log
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=queue_jobs
```

4. Install and prepare the app:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan demo:reset
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. Add a Cloudways cron job:

```bash
* * * * * cd /path/to/tradeloop && php artisan schedule:run >> /dev/null 2>&1
```

6. If queue workers are enabled, run:

```bash
php artisan queue:work --tries=3
```

## Testing

```bash
php artisan test
```

The test suite covers authentication, demo login, business isolation, role restrictions, customers, estimates, invoice payments, job completion automation, simulated sending, and reports.
