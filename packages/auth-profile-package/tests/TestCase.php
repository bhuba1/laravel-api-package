<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests;

use Bhuba\AuthProfilePackage\AuthProfilePackageServiceProvider;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            AuthProfilePackageServiceProvider::class,
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('auth-profile-package.token_ttl', 60);
        $app['config']->set('auth-profile-package.route_prefix', 'api/auth-profile');
        $app['config']->set('auth-profile-package.user_model', User::class);
        $app['config']->set('auth-profile-package.profile_fields', ['id', 'name', 'email']);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => true,
        ]);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('auth.defaults.guard', 'web');
        $app['config']->set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function bearerHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }
}
