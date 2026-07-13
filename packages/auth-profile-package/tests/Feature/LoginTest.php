<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;

class LoginTest extends DatabaseTestCase
{
    public function test_user_can_login_and_receive_token(): void
    {
        $this->createUser([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth-profile/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'expires_at']);

        $this->assertSame(40, strlen((string) $response->json('token')));
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->createUser([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth-profile/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
    }

    public function test_second_login_rotates_existing_token(): void
    {
        $this->createUser([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $firstResponse = $this->postJson('/api/auth-profile/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ])->assertOk();

        $firstToken = (string) $firstResponse->json('token');

        $secondResponse = $this->postJson('/api/auth-profile/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ])->assertOk();

        $secondToken = (string) $secondResponse->json('token');

        $this->assertNotSame($firstToken, $secondToken);

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($firstToken))
            ->assertUnauthorized();

        $this->getJson('/api/auth-profile/profile', $this->bearerHeaders($secondToken))
            ->assertOk();
    }
}
