<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Repositories\TokenRepository;
use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Illuminate\Support\Str;

class ProfileTest extends DatabaseTestCase
{
    public function test_valid_bearer_returns_profile(): void
    {
        $user = $this->createUser();
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->addHour());

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertOk()
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function test_missing_bearer_returns_unauthorized(): void
    {
        $this->getJson('/api/auth-profile/profile')
            ->assertUnauthorized();
    }

    public function test_invalid_bearer_returns_unauthorized(): void
    {
        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders(Str::random(40)))
            ->assertUnauthorized();
    }

    public function test_expired_bearer_returns_unauthorized(): void
    {
        $user = $this->createUser();
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->subMinute());

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertUnauthorized();
    }

    public function test_profile_respects_configured_fields(): void
    {
        config()->set('auth-profile-package.profile_fields', ['id', 'name']);

        $user = $this->createUser();
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->addHour());

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertOk()
            ->assertExactJson([
                'id' => $user->id,
                'name' => $user->name,
            ]);
    }

    public function test_profile_response_is_cached_when_enabled(): void
    {
        config([
            'auth-profile-package.caching.profile.enabled' => true,
            'auth-profile-package.caching.profile.ttl_seconds' => 60,
            'cache.default' => 'array',
        ]);

        $user = $this->createUser(['name' => 'Cached Name']);
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->addHour());

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertOk()
            ->assertJsonPath('name', 'Cached Name');

        $user->forceFill(['name' => 'Updated Name'])->save();

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertOk()
            ->assertJsonPath('name', 'Cached Name');
    }

    public function test_profile_cache_key_changes_when_profile_fields_change(): void
    {
        config([
            'auth-profile-package.caching.profile.enabled' => true,
            'auth-profile-package.caching.profile.ttl_seconds' => 60,
            'auth-profile-package.profile_fields' => ['id', 'name', 'email'],
            'cache.default' => 'array',
        ]);

        $user = $this->createUser(['name' => 'Original Name']);
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->addHour());

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertOk()
            ->assertJsonStructure(['id', 'name', 'email']);

        config()->set('auth-profile-package.profile_fields', ['id', 'name']);

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertOk()
            ->assertExactJson([
                'id' => $user->id,
                'name' => $user->name,
            ]);
    }
}
