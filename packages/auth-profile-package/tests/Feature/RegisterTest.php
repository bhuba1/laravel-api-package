<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;

class RegisterTest extends DatabaseTestCase
{
    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth-profile/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'expires_at']);

        $this->assertSame(40, strlen((string) $response->json('token')));

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::query()->create([
            'name' => 'Existing User',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth-profile/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ])->assertUnprocessable();
    }
}
