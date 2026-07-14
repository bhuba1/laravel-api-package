<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Services;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Bhuba\AuthProfilePackage\Data\TokenResponse;
use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TokenService implements TokenServiceInterface
{
    public function __construct(
        private readonly TokenRepositoryInterface $tokenRepository,
    ) {}

    public function issue(Authenticatable $user): TokenResponse
    {
        return DB::transaction(function () use ($user): TokenResponse {
            if ($this->isSingleTokenMode()) {
                $this->tokenRepository->revokeAllFor($user);
            }

            return $this->createTokenResponse($user);
        });
    }

    public function refresh(Authenticatable $user, ?PersonalAccessToken $currentToken = null): TokenResponse
    {
        return DB::transaction(function () use ($user, $currentToken): TokenResponse {
            if ($this->isSingleTokenMode()) {
                $this->tokenRepository->revokeAllFor($user);
            } elseif ($currentToken instanceof PersonalAccessToken) {
                $this->tokenRepository->revoke($currentToken);
            }

            return $this->createTokenResponse($user);
        });
    }

    public function revoke(PersonalAccessToken $token): void
    {
        $this->tokenRepository->revoke($token);
    }

    private function createTokenResponse(Authenticatable $user): TokenResponse
    {
        $plainTextToken = Str::random(40);
        $expiresAt = $this->resolveExpiresAt();

        $this->tokenRepository->create($user, $plainTextToken, $expiresAt);

        return new TokenResponse(
            token: $plainTextToken,
            expiresAt: $expiresAt->toIso8601String(),
        );
    }

    private function resolveExpiresAt(): CarbonInterface
    {
        return now()->addMinutes((int) config('auth-profile-package.token_ttl'));
    }

    private function isSingleTokenMode(): bool
    {
        return config('auth-profile-package.tokens.mode', 'single') !== 'multiple';
    }
}
