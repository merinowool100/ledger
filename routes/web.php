<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LedgerController;
use App\Models\Ledger;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $ledgers = Ledger::all();
    return view('ledgers.index',compact('ledgers'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('ledgers', LedgerController::class);
});

require __DIR__.'/auth.php';
