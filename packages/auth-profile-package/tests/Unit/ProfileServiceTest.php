<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Contracts\ProfileRepositoryInterface;
use Bhuba\AuthProfilePackage\Services\ProfileService;
use Bhuba\AuthProfilePackage\Support\ProfileResponseCache;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Bhuba\AuthProfilePackage\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;
use Mockery\MockInterface;

class ProfileServiceTest extends TestCase
{
    public function test_get_returns_profile_from_repository(): void
    {
        config(['auth-profile-package.caching.profile.enabled' => false]);

        $user = Mockery::mock(Authenticatable::class);
        $profile = ['id' => 1, 'name' => 'Jane Doe', 'email' => 'jane@example.com'];

        $profileRepository = $this->mockProfileRepository();
        $profileRepository->shouldReceive('buildProfileArray')
            ->once()
            ->with($user)
            ->andReturn($profile);

        $service = new ProfileService($profileRepository, new ProfileResponseCache());

        $this->assertSame($profile, $service->get($user));
    }

    public function test_get_uses_profile_response_cache_when_enabled(): void
    {
        config([
            'auth-profile-package.caching.profile.enabled' => true,
            'auth-profile-package.caching.profile.ttl_seconds' => 60,
            'auth-profile-package.profile_fields' => ['id', 'name'],
            'cache.default' => 'array',
        ]);

        $user = new User([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $user->id = 1;

        $profileRepository = $this->mockProfileRepository();
        $profileRepository->shouldReceive('buildProfileArray')
            ->once()
            ->with($user)
            ->andReturn(['id' => 1, 'name' => 'Jane Doe']);

        $service = new ProfileService($profileRepository, new ProfileResponseCache());

        $this->assertSame(['id' => 1, 'name' => 'Jane Doe'], $service->get($user));
        $this->assertSame(['id' => 1, 'name' => 'Jane Doe'], $service->get($user));
    }

    /**
     * @return MockInterface&ProfileRepositoryInterface
     */
    private function mockProfileRepository(): MockInterface
    {
        return Mockery::mock(ProfileRepositoryInterface::class);
    }
}
