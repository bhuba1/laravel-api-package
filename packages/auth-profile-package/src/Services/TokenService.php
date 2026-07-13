<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Services;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Bhuba\AuthProfilePackage\Data\TokenResponse;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

final class TokenService implements TokenServiceInterface
{
    public function __construct(
        private readonly TokenRepositoryInterface $tokenRepository,
    ) {}

    public function issue(Authenticatable $user): TokenResponse
    {
        $this->tokenRepository->revokeAllFor($user);

        $plainTextToken = Str::random(40);
        $expiresAt = $this->resolveExpiresAt();

        $this->tokenRepository->create($user, $plainTextToken, $expiresAt);

        return new TokenResponse(
            token: $plainTextToken,
            expiresAt: $expiresAt->toIso8601String(),
        );
    }

    public function refresh(Authenticatable $user): TokenResponse
    {
        return $this->issue($user);
    }

    private function resolveExpiresAt(): CarbonInterface
    {
        return now()->addMinutes((int) config('auth-profile-package.token_ttl'));
    }
}
