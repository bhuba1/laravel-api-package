<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Repositories\TokenRepository;
use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Illuminate\Support\Str;

class RevokeTokenTest extends DatabaseTestCase
{
    public function test_valid_bearer_can_revoke_current_token(): void
    {
        $user = $this->createUser();
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->addHour());

        $this->postJson(
            '/api/auth-profile/tokens/revoke',
            [],
            $this->bearerHeaders($plainTextToken),
        )->assertOk()
            ->assertJson(['message' => 'Token revoked.']);

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($plainTextToken))
            ->assertUnauthorized();
    }

    public function test_revoke_requires_bearer_token(): void
    {
        $this->postJson('/api/auth-profile/tokens/revoke')
            ->assertUnauthorized();
    }

    public function test_revoke_rejects_invalid_bearer_token(): void
    {
        $this->postJson(
            '/api/auth-profile/tokens/revoke',
            [],
            $this->bearerHeaders(Str::random(40)),
        )->assertUnauthorized();
    }
}
