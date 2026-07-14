<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Repositories\EloquentProfileRepository;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Bhuba\AuthProfilePackage\Tests\TestCase;

class ProfileRepositoryTest extends TestCase
{
    private EloquentProfileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentProfileRepository();
    }

    public function test_build_for_returns_configured_fields(): void
    {
        config(['auth-profile-package.profile_fields' => ['id', 'name', 'email']]);

        $user = new User([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $user->id = 42;

        $this->assertSame([
            'id' => 42,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ], $this->repository->buildProfileArray($user));
    }

    public function test_build_for_ignores_invalid_field_entries(): void
    {
        config(['auth-profile-package.profile_fields' => ['id', '', 123, 'name']]);

        $user = new User([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $user->id = 42;

        $this->assertSame([
            'id' => 42,
            'name' => 'Jane Doe',
        ], $this->repository->buildProfileArray($user));
    }

    public function test_build_for_falls_back_to_default_fields_when_config_is_invalid(): void
    {
        config(['auth-profile-package.profile_fields' => 'invalid']);

        $user = new User([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $user->id = 42;

        $this->assertSame([
            'id' => 42,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ], $this->repository->buildProfileArray($user));
    }
}
