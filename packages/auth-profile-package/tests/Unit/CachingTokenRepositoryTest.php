<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Bhuba\AuthProfilePackage\Repositories\CachingTokenRepository;
use Bhuba\AuthProfilePackage\Repositories\TokenRepository;
use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;

class CachingTokenRepositoryTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'auth-profile-package.caching.token_validation.enabled' => true,
            'auth-profile-package.caching.token_validation.ttl_seconds' => 60,
            'cache.default' => 'array',
        ]);

        Cache::flush();
    }

    public function test_find_valid_token_uses_cache_on_subsequent_lookups(): void
    {
        $user = $this->createUser(['email' => 'cache-hit@example.com']);
        $plainTextToken = 'cached-token-value-for-testing-purposes';
        $token = new PersonalAccessToken([
            'id' => 1,
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => now()->addHour(),
        ]);
        $token->setRelation('tokenable', $user);

        $inner = $this->mockInnerRepository();
        $inner->shouldReceive('findValidToken')
            ->once()
            ->with($plainTextToken)
            ->andReturn($token);

        $repository = new CachingTokenRepository($inner);

        $first = $repository->findValidToken($plainTextToken);
        $second = $repository->findValidToken($plainTextToken);

        $this->assertInstanceOf(PersonalAccessToken::class, $first);
        $this->assertInstanceOf(PersonalAccessToken::class, $second);
        $this->assertSame($first->token, $second->token);
    }

    public function test_cached_token_rejected_after_expiry(): void
    {
        $user = $this->createUser(['email' => 'cache-expiry@example.com']);
        $plainTextToken = Str::random(40);

        $inner = new TokenRepository();
        $repository = new CachingTokenRepository($inner);

        $inner->create($user, $plainTextToken, now()->addMinute());

        $this->assertNotNull($repository->findValidToken($plainTextToken));

        $this->travel(2)->minutes();

        $this->assertNull($repository->findValidToken($plainTextToken));
        $this->assertNull($repository->findValidToken($plainTextToken));
    }

    public function test_cached_miss_sentinel_still_returns_null(): void
    {
        $plainTextToken = Str::random(40);

        $inner = $this->mockInnerRepository();
        $inner->shouldReceive('findValidToken')
            ->once()
            ->with($plainTextToken)
            ->andReturn(null);

        $repository = new CachingTokenRepository($inner);

        $this->assertNull($repository->findValidToken($plainTextToken));
        $this->assertNull($repository->findValidToken($plainTextToken));
    }

    public function test_revoke_all_for_invalidates_cached_token_lookups(): void
    {
        $user = $this->createUser(['email' => 'cache-bust@example.com']);
        $plainTextToken = Str::random(40);

        $inner = new TokenRepository();
        $repository = new CachingTokenRepository($inner);

        $inner->create($user, $plainTextToken, now()->addHour());

        $this->assertNotNull($repository->findValidToken($plainTextToken));
        $this->assertNotNull($repository->findValidToken($plainTextToken));

        $repository->revokeAllFor($user);

        $this->assertNull($repository->findValidToken($plainTextToken));
    }

    /**
     * @return MockInterface&TokenRepositoryInterface
     */
    private function mockInnerRepository(): MockInterface
    {
        return Mockery::mock(TokenRepositoryInterface::class);
    }
}
