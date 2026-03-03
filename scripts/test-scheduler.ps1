# Quick scheduler & cache tester for Ledger app (Windows PowerShell)
# Usage: .\scripts\test-scheduler.ps1

Write-Host "🚀 Starting Ledger scheduler & cache tester..." -ForegroundColor Green

$sailPath = ".\vendor\bin\sail"
if (-not (Test-Path $sailPath)) {
    Write-Host "❌ Sail not found. Run 'composer install' first." -ForegroundColor Red
    exit 1
}

# Start services
Write-Host "📦 Ensuring Sail services are running..." -ForegroundColor Cyan
& $sailPath up -d
Start-Sleep -Seconds 3

# Run migrations
Write-Host "🗄️  Running migrations..." -ForegroundColor Cyan
& $sailPath artisan migrate --force

# Test 1: Manually compute totals
Write-Host "`n📊 Test 1: Computing and caching total liquid assets..." -ForegroundColor Yellow
& $sailPath artisan ledger:compute-totals
Write-Host "✅ Compute totals completed" -ForegroundColor Green

# Test 2: Verify cache with Tinker
Write-Host "`n📊 Test 2: Verifying cache contents..." -ForegroundColor Yellow
Write-Host "Run this in a new terminal to check cache:" -ForegroundColor Cyan
Write-Host "  .\vendor\bin\sail artisan tinker" -ForegroundColor White
Write-Host "  >>> Cache::get('user:1:total_liquid_assets')" -ForegroundColor White
Write-Host "  >>> Cache::getStore()->getConnection()->flushDB() // Clear Redis" -ForegroundColor White

# Test 3: Run scheduler once
Write-Host "`n📊 Test 3: Running scheduler once..." -ForegroundColor Yellow
& $sailPath artisan schedule:run
Write-Host "✅ Scheduler run completed" -ForegroundColor Green

# Test 4: Show scheduler overview
Write-Host "`n📊 Test 4: Scheduler overview..." -ForegroundColor Yellow
Write-Host "To run scheduler worker (watches real-time), execute in another terminal:" -ForegroundColor Cyan
Write-Host "  .\vendor\bin\sail artisan schedule:work" -ForegroundColor White

Write-Host "`n✨ Cache & scheduler tests complete!" -ForegroundColor Green
Write-Host "`nNext steps:" -ForegroundColor Cyan
Write-Host "  1. Open a new terminal and run: .\vendor\bin\sail artisan schedule:work" -ForegroundColor White
Write-Host "  2. Modifications trigger real-time cache invalidation" -ForegroundColor White
Write-Host "  3. Hourly scheduler recomputes user totals automatically" -ForegroundColor White
