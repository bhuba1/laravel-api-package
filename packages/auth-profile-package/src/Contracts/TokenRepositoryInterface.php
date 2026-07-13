<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;

interface TokenRepositoryInterface
{
    public function create(
        Authenticatable $tokenable,
        string $plainTextToken,
        ?CarbonInterface $expiresAt,
    ): PersonalAccessToken;

    public function findValidToken(string $plainTextToken): ?PersonalAccessToken;

    public function revoke(PersonalAccessToken $token): void;

    public function revokeAllFor(Authenticatable $tokenable): void;
}
