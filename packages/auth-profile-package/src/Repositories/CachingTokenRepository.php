<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Repositories;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

final class CachingTokenRepository implements TokenRepositoryInterface
{
    private const string MISS_SENTINEL = '__auth_profile_token_miss__';

    public function __construct(
        private readonly TokenRepositoryInterface $inner,
    ) {}

    public function create(
        Authenticatable $tokenable,
        string $plainTextToken,
        ?CarbonInterface $expiresAt,
    ): PersonalAccessToken {
        $this->forgetTokenCacheKeysFor($tokenable);

        $token = $this->inner->create($tokenable, $plainTextToken, $expiresAt);
        $this->cache()->forget($this->cacheKey($plainTextToken));

        return $token;
    }

    public function findValidToken(string $plainTextToken): ?PersonalAccessToken
    {
        if (! $this->isEnabled()) {
            return $this->inner->findValidToken($plainTextToken);
        }

        $cacheKey = $this->cacheKey($plainTextToken);
        $cached = $this->cache()->get($cacheKey);

        if ($cached === self::MISS_SENTINEL) {
            return null;
        }

        if ($cached instanceof PersonalAccessToken) {
            return $cached;
        }

        $token = $this->inner->findValidToken($plainTextToken);

        $this->cache()->put(
            $cacheKey,
            $token ?? self::MISS_SENTINEL,
            $this->ttlSeconds(),
        );

        return $token;
    }

    public function revoke(PersonalAccessToken $token): void
    {
        $this->cache()->forget($this->cacheKeyFromStoredHash($token->token));
        $this->inner->revoke($token);
    }

    public function revokeAllFor(Authenticatable $tokenable): void
    {
        $this->forgetTokenCacheKeysFor($tokenable);
        $this->inner->revokeAllFor($tokenable);
    }

    private function forgetTokenCacheKeysFor(Authenticatable $tokenable): void
    {
        $tokens = PersonalAccessToken::query()
            ->where('tokenable_type', $tokenable->getMorphClass())
            ->where('tokenable_id', $tokenable->getAuthIdentifier())
            ->pluck('token');

        foreach ($tokens as $storedHash) {
            if (is_string($storedHash) && $storedHash !== '') {
                $this->cache()->forget($this->cacheKeyFromStoredHash($storedHash));
            }
        }
    }

    private function cacheKey(string $plainTextToken): string
    {
        return $this->cacheKeyFromStoredHash(hash('sha256', $plainTextToken));
    }

    private function cacheKeyFromStoredHash(string $storedHash): string
    {
        return 'auth-profile-package:token:'.$storedHash;
    }

    private function isEnabled(): bool
    {
        return (bool) config('auth-profile-package.caching.token_validation.enabled', false);
    }

    private function ttlSeconds(): int
    {
        return (int) config('auth-profile-package.caching.token_validation.ttl_seconds', 60);
    }

    private function cache(): CacheRepository
    {
        $store = config('auth-profile-package.caching.store');

        if (is_string($store) && $store !== '') {
            return Cache::store($store);
        }

        return Cache::store((string) config('cache.default', 'array'));
    }
}
