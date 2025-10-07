<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Transaction\IndexController;
use App\Http\Controllers\Transaction\StoreController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 👇 Redirect root route to /transactions
Route::redirect('/', '/transactions');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/transactions', IndexController::class)->name('transactions');
    Route::post('/transactions', StoreController::class)->name('transactions.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
require __DIR__.'/channels.php';
