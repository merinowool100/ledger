# Local Development Guide

This guide covers setting up the Ledger app locally with Docker (Sail) for development, testing, and scheduler verification.

## Prerequisites

- Docker Desktop installed
- Git
- Composer (optional, Docker handles it)

## Quick Start with Sail

### 1. Build and Start Docker Containers

```bash
# Navigate to project directory
cd Ledger

# Start Sail services (mysql, redis, laravel.test, etc.)
./vendor/bin/sail up -d

# On Windows PowerShell, use:
# vendor\bin\sail up -d
```

### 2. Install Dependencies (if needed)

```bash
./vendor/bin/sail composer install
```

### 3. Run Migrations

```bash
./vendor/bin/sail artisan migrate --force
```

## Running Tests

### Run Full Test Suite

```bash
./vendor/bin/sail artisan test
```

### Run Specific Test File

```bash
./vendor/bin/sail artisan test tests/Feature/SyncTest.php
./vendor/bin/sail artisan test tests/Feature/AccountTest.php
```

### Run with Coverage Report

```bash
./vendor/bin/sail artisan test --coverage
```

## Testing the Cache & Scheduler

### 1. Manually Compute Total Assets (Once)

```bash
# This will compute and cache total liquid assets for all users
./vendor/bin/sail artisan ledger:compute-totals
```

Check the cache:
```bash
# Open Laravel Tinker shell
./vendor/bin/sail artisan tinker

# In Tinker:
Cache::get('user:1:total_liquid_assets')
```

### 2. Run Scheduler Worker (Watch Hourly Commands)

Open a new terminal and run:

```bash
# This watches the schedule and runs commands as needed
./vendor/bin/sail artisan schedule:work
```

You should see output like:
```
Scheduling work ... 
Running scheduled command: ledger:compute-totals
```

### 3. Test Scheduler Execution Manually

```bash
# Force execute the schedule
./vendor/bin/sail artisan schedule:run
```

## Redis Cache Configuration

To use Redis for caching (recommended for production), add to `.env`:

```
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Then test:

```bash
# Clear all cache
./vendor/bin/sail artisan cache:clear

# Compute totals (will cache in Redis)
./vendor/bin/sail artisan ledger:compute-totals

# Verify in Redis CLI
./vendor/bin/sail redis-cli
# In Redis CLI: keys *
```

## Stopping Services

```bash
# Stop all containers
./vendor/bin/sail down

# On Windows PowerShell:
# vendor\bin\sail down
```

## GitHub Actions CI

The project includes a CI workflow (`.github/workflows/phpunit.yml`) that:

1. Checks out code
2. Sets up PHP 8.1
3. Installs Composer dependencies
4. Prepares SQLite in-memory database
5. Runs migrations
6. Executes tests with coverage

**To trigger the workflow:** Push to the `main` branch or open a pull request.

Workflow results are visible on GitHub under **Actions** tab in your repository.

## Troubleshooting

### Containers fail to start

```bash
# Check logs
./vendor/bin/sail logs

# Rebuild containers
./vendor/bin/sail build
```

### Database locked errors

```bash
# Reset database
./vendor/bin/sail artisan migrate:reset
./vendor/bin/sail artisan migrate --force
```

### Cache not working

```bash
# Clear cache
./vendor/bin/sail artisan cache:clear

# Verify Redis connection
./vendor/bin/sail redis-cli ping
```

## Key Commands Summary

| Command | Purpose |
|---------|---------|
| `./vendor/bin/sail up -d` | Start all services |
| `./vendor/bin/sail down` | Stop all services |
| `./vendor/bin/sail artisan migrate` | Run database migrations |
| `./vendor/bin/sail artisan test` | Run test suite |
| `./vendor/bin/sail artisan ledger:compute-totals` | Compute and cache user totals |
| `./vendor/bin/sail artisan schedule:work` | Run scheduler worker |
| `./vendor/bin/sail tinker` | Interactive shell |
| `./vendor/bin/sail redis-cli` | Redis command-line client |

