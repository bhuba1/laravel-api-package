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
        config(['auth-profile-package.token_ttl' => 120]);

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

    public function test_refresh_delegates_to_issue(): void
    {
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

    /**
     * @return MockInterface&TokenRepositoryInterface
     */
    private function mockRepository(): MockInterface
    {
        return Mockery::mock(TokenRepositoryInterface::class);
    }
}
