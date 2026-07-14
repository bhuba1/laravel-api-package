<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitingTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('auth-profile-login');
        RateLimiter::clear('auth-profile-register');
        RateLimiter::clear('auth-profile-profile');
        RateLimiter::clear('auth-profile-refresh');
        RateLimiter::clear('auth-profile-revoke');
    }

    public function test_login_is_rate_limited_after_max_attempts(): void
    {
        config([
            'auth-profile-package.rate_limiting.enabled' => true,
            'auth-profile-package.rate_limiting.login' => [
                'max_attempts' => 2,
                'decay_minutes' => 1,
            ],
        ]);

        $this->createUser([
            'email' => 'rate-limit@example.com',
            'password' => 'password123',
        ]);

        $payload = [
            'email' => 'rate-limit@example.com',
            'password' => 'wrong-password',
        ];

        $this->postJson('/api/auth-profile/login', $payload)->assertUnprocessable();
        $this->postJson('/api/auth-profile/login', $payload)->assertUnprocessable();

        $response = $this->postJson('/api/auth-profile/login', $payload);

        $response->assertTooManyRequests()
            ->assertExactJson(['message' => 'Too many requests. Please try again later.'])
            ->assertJsonMissing(['exception', 'trace']);
    }

    public function test_register_is_rate_limited_after_max_attempts(): void
    {
        config([
            'auth-profile-package.rate_limiting.enabled' => true,
            'auth-profile-package.rate_limiting.register' => [
                'max_attempts' => 2,
                'decay_minutes' => 1,
            ],
        ]);

        $this->postJson('/api/auth-profile/register', [
            'name' => 'User One',
            'email' => 'register-one@example.com',
            'password' => 'password123',
        ])->assertCreated();

        $this->postJson('/api/auth-profile/register', [
            'name' => 'User Two',
            'email' => 'register-two@example.com',
            'password' => 'password123',
        ])->assertCreated();

        $this->postJson('/api/auth-profile/register', [
            'name' => 'User Three',
            'email' => 'register-three@example.com',
            'password' => 'password123',
        ])
            ->assertTooManyRequests()
            ->assertExactJson(['message' => 'Too many requests. Please try again later.'])
            ->assertJsonMissing(['exception', 'trace']);
    }

    public function test_rate_limiting_can_be_disabled(): void
    {
        config([
            'auth-profile-package.rate_limiting.enabled' => false,
            'auth-profile-package.rate_limiting.login' => [
                'max_attempts' => 1,
                'decay_minutes' => 1,
            ],
        ]);

        $this->createUser([
            'email' => 'no-limit@example.com',
            'password' => 'password123',
        ]);

        $payload = [
            'email' => 'no-limit@example.com',
            'password' => 'wrong-password',
        ];

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/auth-profile/login', $payload)->assertUnprocessable();
        }
    }
}
