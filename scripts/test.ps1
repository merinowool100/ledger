# Quick test runner for Ledger app (Windows PowerShell)
# Usage: .\scripts\test.ps1

Write-Host "🚀 Starting Ledger test runner..." -ForegroundColor Green

# Check if Sail is available
$sailPath = ".\vendor\bin\sail"
if (-not (Test-Path $sailPath)) {
    Write-Host "❌ Sail not found. Run 'composer install' first." -ForegroundColor Red
    exit 1
}

# Start services if not already running
Write-Host "📦 Ensuring Sail services are running..." -ForegroundColor Cyan
& $sailPath up -d

# Wait for services
Write-Host "⏳ Waiting for services to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

# Run migrations
Write-Host "🗄️  Running migrations..." -ForegroundColor Cyan
& $sailPath artisan migrate --force

# Run tests
Write-Host "✅ Running test suite..." -ForegroundColor Green
& $sailPath artisan test --no-tty

# Run specific tests if requested
if ($args.Length -gt 0) {
    Write-Host "🔍 Running specific test: $($args[0])" -ForegroundColor Cyan
    & $sailPath artisan test $args[0] --no-tty
}

Write-Host "✨ Tests complete!" -ForegroundColor Green
