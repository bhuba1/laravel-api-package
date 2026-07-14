<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Bhuba\AuthProfilePackage\Services\TokenService;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Bhuba\AuthProfilePackage\Tests\TestCase;
use Carbon\CarbonInterface;
use Mockery;
use Mockery\MockInterface;

class TokenServiceTest extends TestCase
{
    public function test_issue_returns_plain_token_and_expires_at(): void
    {
        config(['auth-profile-package.tokens.mode' => 'single']);

        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->id = 1;

        $repository = $this->mockRepository();
        $repository->shouldReceive('revokeAllFor')->once()->with($user);
        $repository->shouldReceive('create')
            ->once()
            ->with(
                $user,
                Mockery::type('string'),
                Mockery::type(CarbonInterface::class),
            )
            ->andReturn(new PersonalAccessToken());

        $service = new TokenService($repository);
        $result = $service->issue($user);

        $this->assertSame(40, strlen($result->token));
        $this->assertNotSame('', $result->expiresAt);
    }

    public function test_issue_uses_configured_token_ttl(): void
    {
        config([
            'auth-profile-package.token_ttl' => 120,
            'auth-profile-package.tokens.mode' => 'single',
        ]);

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->id = 1;

        $repository = $this->mockRepository();
        $repository->shouldReceive('revokeAllFor')->once();
        $repository->shouldReceive('create')
            ->once()
            ->with(
                $user,
                Mockery::type('string'),
                Mockery::on(static fn (CarbonInterface $expiresAt): bool => $expiresAt->greaterThan(now()->addMinutes(119))
                    && $expiresAt->lessThanOrEqualTo(now()->addMinutes(120))),
            )
            ->andReturn(new PersonalAccessToken());

        $service = new TokenService($repository);
        $service->issue($user);
    }

    public function test_refresh_delegates_to_issue_in_single_mode(): void
    {
        config(['auth-profile-package.tokens.mode' => 'single']);

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->id = 1;

        $repository = $this->mockRepository();
        $repository->shouldReceive('revokeAllFor')->once()->with($user);
        $repository->shouldReceive('create')
            ->once()
            ->andReturn(new PersonalAccessToken());

        $service = new TokenService($repository);
        $result = $service->refresh($user);

        $this->assertNotSame('', $result->token);
        $this->assertNotSame('', $result->expiresAt);
    }

    public function test_issue_does_not_revoke_existing_tokens_in_multiple_mode(): void
    {
        config(['auth-profile-package.tokens.mode' => 'multiple']);

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->id = 1;

        $repository = $this->mockRepository();
        $repository->shouldReceive('revokeAllFor')->never();
        $repository->shouldReceive('create')
            ->once()
            ->andReturn(new PersonalAccessToken());

        $service = new TokenService($repository);
        $service->issue($user);
    }

    public function test_refresh_revokes_only_current_token_in_multiple_mode(): void
    {
        config(['auth-profile-package.tokens.mode' => 'multiple']);

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->id = 1;
        $currentToken = new PersonalAccessToken(['id' => 10]);

        $repository = $this->mockRepository();
        $repository->shouldReceive('revokeAllFor')->never();
        $repository->shouldReceive('revoke')->once()->with($currentToken);
        $repository->shouldReceive('create')
            ->once()
            ->andReturn(new PersonalAccessToken());

        $service = new TokenService($repository);
        $service->refresh($user, $currentToken);
    }

    public function test_revoke_delegates_to_repository(): void
    {
        $token = new PersonalAccessToken(['id' => 5]);

        $repository = $this->mockRepository();
        $repository->shouldReceive('revoke')->once()->with($token);

        $service = new TokenService($repository);
        $service->revoke($token);
    }

    /**
     * @return MockInterface&TokenRepositoryInterface
     */
    private function mockRepository(): MockInterface
    {
        return Mockery::mock(TokenRepositoryInterface::class);
    }
}
