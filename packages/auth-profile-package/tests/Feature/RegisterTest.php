<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Illuminate\Support\Facades\Hash;

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

        $user = User::query()->where('email', 'jane@example.com')->first();
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue(Hash::check('password123', (string) $user->password));
    }

    public function test_register_accepts_custom_configured_fields(): void
    {
        config([
            'auth-profile-package.register_fields' => ['username', 'email', 'password'],
            'auth-profile-package.register_field_rules' => [
                'username' => ['required', 'string', 'max:50', 'alpha_dash'],
            ],
        ]);

        $response = $this->postJson('/api/auth-profile/register', [
            'username' => 'janedoe',
            'email' => 'custom@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'expires_at']);

        $this->assertDatabaseHas('users', [
            'username' => 'janedoe',
            'email' => 'custom@example.com',
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
