<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\UserRepositoryInterface;
use Bhuba\AuthProfilePackage\Data\LoginCredentials;
use Bhuba\AuthProfilePackage\Data\RegisterCredentials;
use Bhuba\AuthProfilePackage\Data\TokenResponse;
use Bhuba\AuthProfilePackage\Services\AuthService;
use Bhuba\AuthProfilePackage\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;

class AuthServiceTest extends TestCase
{
    public function test_login_returns_token_response_for_valid_credentials(): void
    {
        $user = $this->mockAuthenticatable(Hash::make('password123'));

        $userRepository = $this->mockUserRepository();
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('login@example.com')
            ->andReturn($user);

        $tokenResponse = new TokenResponse('plain-text-token', '2026-07-10T14:20:00+00:00');

        $tokenService = $this->mockTokenService();
        $tokenService->shouldReceive('issue')
            ->once()
            ->with($user)
            ->andReturn($tokenResponse);

        $service = new AuthService($userRepository, $tokenService);

        $result = $service->login(new LoginCredentials('login@example.com', 'password123'));

        $this->assertSame($tokenResponse, $result);
    }

    public function test_login_throws_validation_exception_for_wrong_password(): void
    {
        $user = $this->mockAuthenticatable(Hash::make('password123'));

        $userRepository = $this->mockUserRepository();
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('login@example.com')
            ->andReturn($user);

        $tokenService = $this->mockTokenService();

        $service = new AuthService($userRepository, $tokenService);

        $this->expectException(ValidationException::class);

        $service->login(new LoginCredentials('login@example.com', 'wrong-password'));
    }

    public function test_register_creates_user_and_returns_token_response(): void
    {
        $user = $this->mockAuthenticatable('hashed-password');

        $userRepository = $this->mockUserRepository();
        $userRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $attributes): bool {
                return $attributes['name'] === 'Jane Doe'
                    && $attributes['email'] === 'jane@example.com'
                    && Hash::check('password123', (string) $attributes['password']);
            }))
            ->andReturn($user);

        $tokenResponse = new TokenResponse('plain-text-token', '2026-07-10T14:20:00+00:00');

        $tokenService = $this->mockTokenService();
        $tokenService->shouldReceive('issue')
            ->once()
            ->with($user)
            ->andReturn($tokenResponse);

        $service = new AuthService($userRepository, $tokenService);

        $result = $service->register(new RegisterCredentials([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]));

        $this->assertSame($tokenResponse, $result);
    }

    /**
     * @return MockInterface&Authenticatable
     */
    private function mockAuthenticatable(string $hashedPassword): MockInterface
    {
        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getAuthPassword')->andReturn($hashedPassword);

        return $user;
    }

    /**
     * @return MockInterface&UserRepositoryInterface
     */
    private function mockUserRepository(): MockInterface
    {
        return Mockery::mock(UserRepositoryInterface::class);
    }

    /**
     * @return MockInterface&TokenServiceInterface
     */
    private function mockTokenService(): MockInterface
    {
        return Mockery::mock(TokenServiceInterface::class);
    }
}
