<?php

use App\Http\Controllers\Api\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\RegisteredUserController;
use App\Http\Controllers\Api\General\AccountsController;
use App\Http\Controllers\Api\General\NotificationController;
use App\Http\Controllers\Api\General\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function Ramsey\Uuid\v1;




Route::prefix('/v1')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
    });

    Route::prefix('/auth')->group(function () {
        Route::post('/generate-verification-url', [EmailVerificationController::class, 'generateVerificationUrl'])->name('verification.getVerificationUrl');
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
            ->name('verification.verify');
    });
    Route::middleware('auth:sanctum', 'verified')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::resource('accounts', AccountsController::class)->except('edit');
        Route::resource('transactions', TransactionController::class)->except('edit', 'update');
        Route::get('/transactions/currencies/{id}', [TransactionController::class, 'getTransactionsByCurrency']);
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    });
});
