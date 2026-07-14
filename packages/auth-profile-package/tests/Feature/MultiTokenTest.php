<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;

class MultiTokenTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['auth-profile-package.tokens.mode' => 'multiple']);
    }

    public function test_multiple_logins_keep_all_tokens_valid(): void
    {
        $this->createUser([
            'email' => 'multi@example.com',
            'password' => 'password123',
        ]);

        $firstResponse = $this->postJson('/api/auth-profile/login', [
            'email' => 'multi@example.com',
            'password' => 'password123',
        ])->assertOk();

        $secondResponse = $this->postJson('/api/auth-profile/login', [
            'email' => 'multi@example.com',
            'password' => 'password123',
        ])->assertOk();

        $firstToken = (string) $firstResponse->json('token');
        $secondToken = (string) $secondResponse->json('token');

        $this->assertNotSame($firstToken, $secondToken);

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($firstToken))
            ->assertOk();

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($secondToken))
            ->assertOk();
    }

    public function test_refresh_in_multiple_mode_revokes_only_presented_token(): void
    {
        $this->createUser([
            'email' => 'multi-refresh@example.com',
            'password' => 'password123',
        ]);

        $firstResponse = $this->postJson('/api/auth-profile/login', [
            'email' => 'multi-refresh@example.com',
            'password' => 'password123',
        ])->assertOk();

        $secondResponse = $this->postJson('/api/auth-profile/login', [
            'email' => 'multi-refresh@example.com',
            'password' => 'password123',
        ])->assertOk();

        $firstToken = (string) $firstResponse->json('token');
        $secondToken = (string) $secondResponse->json('token');

        $refreshResponse = $this->postJson(
            '/api/auth-profile/tokens/refresh',
            [],
            $this->bearerHeaders($firstToken),
        )->assertOk();

        $refreshedToken = (string) $refreshResponse->json('token');

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($firstToken))
            ->assertUnauthorized();

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($refreshedToken))
            ->assertOk();

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($secondToken))
            ->assertOk();
    }
}
