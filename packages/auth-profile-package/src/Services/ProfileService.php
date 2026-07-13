<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Services;

use Bhuba\AuthProfilePackage\Contracts\ProfileRepositoryInterface;
use Bhuba\AuthProfilePackage\Contracts\ProfileServiceInterface;
use Bhuba\AuthProfilePackage\Support\ProfileResponseCache;
use Illuminate\Contracts\Auth\Authenticatable;

final class ProfileService implements ProfileServiceInterface
{
    public function __construct(
        private readonly ProfileRepositoryInterface $profileRepository,
        private readonly ProfileResponseCache $profileResponseCache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function get(Authenticatable $user): array
    {
        return $this->profileResponseCache->remember(
            $user,
            fn (): array => $this->profileRepository->buildFor($user),
        );
    }
}
