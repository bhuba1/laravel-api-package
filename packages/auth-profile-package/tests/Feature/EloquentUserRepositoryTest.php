<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;
use Bhuba\AuthProfilePackage\Repositories\EloquentUserRepository;
use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Illuminate\Contracts\Auth\Authenticatable;

class EloquentUserRepositoryTest extends DatabaseTestCase
{
    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentUserRepository(
            $this->app->make(UserModelResolverInterface::class),
        );
    }

    public function test_find_by_email_returns_matching_user(): void
    {
        $user = $this->createUser([
            'email' => 'repo@example.com',
            'password' => 'password123',
        ]);

        $found = $this->repository->findByEmail('repo@example.com');

        $this->assertInstanceOf(Authenticatable::class, $found);
        $this->assertTrue($found->is($user));
    }

    public function test_find_by_email_returns_null_when_user_does_not_exist(): void
    {
        $this->assertNull($this->repository->findByEmail('missing@example.com'));
    }

    public function test_create_persists_user_with_given_attributes(): void
    {
        $user = $this->repository->create([
            'name' => 'Repository User',
            'email' => 'created@example.com',
            'password' => 'password123',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'Repository User',
            'email' => 'created@example.com',
        ]);
    }
}
