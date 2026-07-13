<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Support;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

final class ProfileResponseCache
{
    /**
     * @return array<string, mixed>
     */
    public function remember(Authenticatable $user, Closure $resolver): array
    {
        if (! $this->isEnabled()) {
            return $resolver();
        }

        return $this->cache()->remember(
            $this->cacheKey($user),
            $this->ttlSeconds(),
            $resolver,
        );
    }

    private function isEnabled(): bool
    {
        return (bool) config('auth-profile-package.caching.profile.enabled', false);
    }

    private function ttlSeconds(): int
    {
        return (int) config('auth-profile-package.caching.profile.ttl_seconds', 60);
    }

    private function cacheKey(Authenticatable $user): string
    {
        $fields = config('auth-profile-package.profile_fields', ['id', 'name', 'email']);

        if (! is_array($fields)) {
            $fields = ['id', 'name', 'email'];
        }

        return sprintf(
            'auth-profile-package:profile:%s:%s',
            (string) $user->getAuthIdentifier(),
            md5((string) json_encode($fields)),
        );
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
