#!/usr/bin/env bash

# Quick test runner for Ledger app
# Usage: ./scripts/test.sh

set -e

echo "🚀 Starting Ledger test runner..."

# Check if Sail is available
if ! command -v sail &> /dev/null && [ ! -f ./vendor/bin/sail ]; then
    echo "❌ Sail not found. Run 'composer install' first."
    exit 1
fi

SAIL="./vendor/bin/sail"

# Start services if not already running
echo "📦 Ensuring Sail services are running..."
$SAIL up -d

# Wait for services
echo "⏳ Waiting for services to be ready..."
sleep 3

# Run migrations
echo "🗄️  Running migrations..."
$SAIL artisan migrate --force

# Run tests
echo "✅ Running test suite..."
$SAIL artisan test --no-tty

# Run specific tests if requested
if [ "$1" != "" ]; then
    echo "🔍 Running specific test: $1"
    $SAIL artisan test "$1" --no-tty
fi

echo "✨ Tests complete!"
