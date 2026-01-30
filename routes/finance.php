<?php

use App\Http\Controllers\Finance\UserBalanceController;
use App\Http\Controllers\Finance\UserBalanceTransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/finance/balance', [UserBalanceController::class, 'show']);
    Route::get('/finance/transactions', [UserBalanceTransactionController::class, 'index']);
    Route::get('/finance/transactions/history', function () {
        return Inertia::render('finance/TransactionsHistory');
    });

});
