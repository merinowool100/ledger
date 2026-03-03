# Ledger on Sakura server

## Description
Laravelで作った家計簿アプリをさくらサーバー上にデプロイしました

## URL
https://n442.sakura.ne.jp/ledger/ledgers

## Note
なんとかかんとかLaravelで作りつつ、デザインやロゴはまだLaravel仕様のままです

## Development: caching, scheduler and CI

Quick notes to enable improved performance and background processing:

- To use Redis for caching (recommended in production), set in your `.env`:

	- `CACHE_DRIVER=redis`
	- configure `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` as appropriate

- To precompute total liquid assets for users periodically, this repo includes an artisan command:

	- `php artisan ledger:compute-totals`

	Run it manually, or add it to your scheduler (register the command in `app/Console/Kernel.php` schedule):

	`$schedule->command('ledger:compute-totals')->hourly();`

- CI: a GitHub Actions workflow `/.github/workflows/phpunit.yml` is included to run tests using SQLite in-memory.

If you'd like, I can add the scheduler registration to `app/Console/Kernel.php` for you and wire up Redis in the Sail config.
