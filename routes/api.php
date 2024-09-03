<?php

use App\Http\Controllers\Api\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\RegisteredUserController;
use App\Http\Controllers\Api\General\AccountsController;
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
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::resource('accounts', AccountsController::class)->except('edit');
    });
});
