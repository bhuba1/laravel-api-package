<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Repositories\TokenRepository;
use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Illuminate\Support\Str;

class RefreshTokenTest extends DatabaseTestCase
{
    public function test_valid_bearer_can_refresh_token_without_host_auth(): void
    {
        $user = $this->createUser();
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->addHour());

        $response = $this->postJson(
            '/api/auth-profile/tokens/refresh',
            [],
            $this->bearerHeaders($plainTextToken),
        );

        $response->assertOk()
            ->assertJsonStructure(['token', 'expires_at']);

        $newToken = (string) $response->json('token');

        $this->assertNotSame($plainTextToken, $newToken);

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertUnauthorized();

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($newToken))
            ->assertOk()
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function test_refresh_requires_bearer_token(): void
    {
        $this->postJson('/api/auth-profile/tokens/refresh')
            ->assertUnauthorized();
    }

    public function test_refresh_rejects_invalid_bearer_token(): void
    {
        $this->postJson(
            '/api/auth-profile/tokens/refresh',
            [],
            $this->bearerHeaders(Str::random(40)),
        )->assertUnauthorized();
    }

    public function test_refresh_rejects_expired_bearer_token(): void
    {
        $user = $this->createUser();
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->subMinute());

        $this->postJson(
            '/api/auth-profile/tokens/refresh',
            [],
            $this->bearerHeaders($plainTextToken),
        )->assertUnauthorized();
    }
}
