<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LedgerController;
use App\Models\Ledger;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\AccountController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard',[LedgerController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // custom ledger routes before resource to avoid collision with {ledger} wildcard
    Route::get('/ledgers/export', [LedgerController::class, 'export'])->name('ledgers.export');
    Route::post('/ledgers/{ledger}/confirm', [LedgerController::class, 'confirm'])->name('ledgers.confirm');
    Route::patch('/ledgers/{ledger}/update-inline', [LedgerController::class, 'updateInline'])->name('ledgers.updateInline');
    // apply constraint so {ledger} only matches numeric ids
    Route::resource('ledgers', LedgerController::class)->where(['ledger' => '[0-9]+']);

    // offline sync endpoints
    Route::prefix('sync')->group(function () {
        Route::get('/', [SyncController::class, 'fetch']);
        Route::post('/', [SyncController::class, 'push']);
        // fetch a single transaction by transaction_id (for conflict resolution)
        Route::get('tx/{transaction_id}', [SyncController::class, 'showTransaction']);
    });

    // account management
    Route::resource('accounts', AccountController::class)->only(['index','store','update','destroy']);
});


require __DIR__.'/auth.php';


