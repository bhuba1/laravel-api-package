<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Support;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class ConfigureRateLimiting
{
    public static function register(): void
    {
        RateLimiter::for('auth-profile-login', static function (Request $request): Limit {
            if (! config('auth-profile-package.rate_limiting.enabled', true)) {
                return Limit::none();
            }

            /** @var array{max_attempts: int, decay_minutes: int} $settings */
            $settings = config('auth-profile-package.rate_limiting.login', [
                'max_attempts' => 5,
                'decay_minutes' => 1,
            ]);

            $email = (string) $request->input('email', '');

            return Limit::perMinutes(
                (int) $settings['decay_minutes'],
                (int) $settings['max_attempts'],
            )->by($request->ip().'|'.$email);
        });

        RateLimiter::for('auth-profile-register', static function (Request $request): Limit {
            if (! config('auth-profile-package.rate_limiting.enabled', true)) {
                return Limit::none();
            }

            /** @var array{max_attempts: int, decay_minutes: int} $settings */
            $settings = config('auth-profile-package.rate_limiting.register', [
                'max_attempts' => 5,
                'decay_minutes' => 1,
            ]);

            return Limit::perMinutes(
                (int) $settings['decay_minutes'],
                (int) $settings['max_attempts'],
            )->by((string) $request->ip());
        });

        RateLimiter::for('auth-profile-profile', static function (Request $request): Limit {
            if (! config('auth-profile-package.rate_limiting.enabled', true)) {
                return Limit::none();
            }

            /** @var array{max_attempts: int, decay_minutes: int} $settings */
            $settings = config('auth-profile-package.rate_limiting.profile', [
                'max_attempts' => 60,
                'decay_minutes' => 1,
            ]);

            $user = $request->user();
            $key = $user !== null ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinutes(
                (int) $settings['decay_minutes'],
                (int) $settings['max_attempts'],
            )->by($key);
        });

        RateLimiter::for('auth-profile-refresh', static function (Request $request): Limit {
            if (! config('auth-profile-package.rate_limiting.enabled', true)) {
                return Limit::none();
            }

            /** @var array{max_attempts: int, decay_minutes: int} $settings */
            $settings = config('auth-profile-package.rate_limiting.refresh', [
                'max_attempts' => 10,
                'decay_minutes' => 1,
            ]);

            $user = $request->user();
            $key = $user !== null ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinutes(
                (int) $settings['decay_minutes'],
                (int) $settings['max_attempts'],
            )->by($key);
        });

        RateLimiter::for('auth-profile-revoke', static function (Request $request): Limit {
            if (! config('auth-profile-package.rate_limiting.enabled', true)) {
                return Limit::none();
            }

            /** @var array{max_attempts: int, decay_minutes: int} $settings */
            $settings = config('auth-profile-package.rate_limiting.revoke', [
                'max_attempts' => 10,
                'decay_minutes' => 1,
            ]);

            $user = $request->user();
            $key = $user !== null ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinutes(
                (int) $settings['decay_minutes'],
                (int) $settings['max_attempts'],
            )->by($key);
        });
    }
}
