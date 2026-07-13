<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests;

use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PDO;

abstract class DatabaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped(
                'The pdo_sqlite extension is required for database tests. '.
                'On Arch/CachyOS install: sudo pacman -S php-sqlite. '.
                'Or run: composer test:docker',
            );
        }

        parent::setUp();
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ], $attributes));
    }
}
