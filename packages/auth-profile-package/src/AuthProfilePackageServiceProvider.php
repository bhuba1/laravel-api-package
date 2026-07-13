<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage;

use Bhuba\AuthProfilePackage\Console\Commands\InstallCommand;
use Bhuba\AuthProfilePackage\Contracts\AuthServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\ProfileRepositoryInterface;
use Bhuba\AuthProfilePackage\Contracts\ProfileServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;
use Bhuba\AuthProfilePackage\Contracts\UserRepositoryInterface;
use Bhuba\AuthProfilePackage\Http\Middleware\ValidatePackageToken;
use Bhuba\AuthProfilePackage\Repositories\CachingTokenRepository;
use Bhuba\AuthProfilePackage\Repositories\EloquentProfileRepository;
use Bhuba\AuthProfilePackage\Repositories\EloquentUserRepository;
use Bhuba\AuthProfilePackage\Repositories\TokenRepository;
use Bhuba\AuthProfilePackage\Services\AuthService;
use Bhuba\AuthProfilePackage\Services\ProfileService;
use Bhuba\AuthProfilePackage\Services\TokenService;
use Bhuba\AuthProfilePackage\Support\ConfigureRateLimiting;
use Bhuba\AuthProfilePackage\Support\RegisterPackageExceptionRendering;
use Bhuba\AuthProfilePackage\Support\UserModelResolver;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\ServiceProvider;

class AuthProfilePackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/auth-profile-package.php',
            'auth-profile-package',
        );

        $this->app->bind(TokenRepositoryInterface::class, function (): TokenRepositoryInterface {
            $inner = new TokenRepository();

            if (config('auth-profile-package.caching.token_validation.enabled', false)) {
                return new CachingTokenRepository($inner);
            }

            return $inner;
        });
        $this->app->bind(TokenServiceInterface::class, TokenService::class);
        $this->app->bind(UserModelResolverInterface::class, UserModelResolver::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/auth-profile-package.php' => config_path('auth-profile-package.php'),
            ], 'auth-profile-package-config');

            if (method_exists($this, 'publishesMigrations')) {
                $this->publishesMigrations([
                    __DIR__.'/../database/migrations' => database_path('migrations'),
                ], 'auth-profile-package-migrations');
            } else {
                $this->publishes([
                    __DIR__.'/../database/migrations' => database_path('migrations'),
                ], 'auth-profile-package-migrations');
            }

            $this->commands([
                InstallCommand::class,
            ]);
        }

        ConfigureRateLimiting::register();

        $this->callAfterResolving(Handler::class, static function (Handler $handler): void {
            RegisterPackageExceptionRendering::register($handler);
        });

        $this->app['router']->aliasMiddleware('auth-profile.token', ValidatePackageToken::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        if (class_exists(AboutCommand::class)) {
            AboutCommand::add('Auth Profile Package', fn (): array => [
                'Token TTL' => config('auth-profile-package.token_ttl').' min',
                'Route Prefix' => config('auth-profile-package.route_prefix'),
                'User Model' => config('auth-profile-package.user_model'),
                'Rate Limiting' => config('auth-profile-package.rate_limiting.enabled', true) ? 'enabled' : 'disabled',
                'Profile Cache' => config('auth-profile-package.caching.profile.enabled', false) ? 'enabled' : 'disabled',
                'Token Validation Cache' => config('auth-profile-package.caching.token_validation.enabled', false) ? 'enabled' : 'disabled',
            ]);
        }
    }
}
