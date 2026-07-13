<?php

declare(strict_types=1);

use Bhuba\AuthProfilePackage\Http\Controllers\AuthController;
use Bhuba\AuthProfilePackage\Http\Controllers\ProfileController;
use Bhuba\AuthProfilePackage\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix((string) config('auth-profile-package.route_prefix'))
    ->middleware('api')
    ->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:auth-profile-register');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth-profile-login');
        Route::post('/tokens/refresh', [TokenController::class, 'refresh'])
            ->middleware(['auth-profile.token', 'throttle:auth-profile-refresh']);
        Route::get('/profile', [ProfileController::class, 'show'])
            ->middleware(['auth-profile.token', 'throttle:auth-profile-profile']);
    });
