<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Repositories;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;

final class TokenRepository implements TokenRepositoryInterface
{
    public function create(
        Authenticatable $tokenable,
        string $plainTextToken,
        ?CarbonInterface $expiresAt,
    ): PersonalAccessToken {
        return PersonalAccessToken::query()->create([
            'tokenable_type' => $tokenable->getMorphClass(),
            'tokenable_id' => $tokenable->getAuthIdentifier(),
            'token' => $this->hashToken($plainTextToken),
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);
    }

    public function findValidToken(string $plainTextToken): ?PersonalAccessToken
    {
        $token = PersonalAccessToken::query()
            ->where('token', $this->hashToken($plainTextToken))
            ->first();

        if ($token === null) {
            return null;
        }

        if ($token->expires_at !== null && $token->expires_at->isPast()) {
            return null;
        }

        if ($token->tokenable === null) {
            return null;
        }

        return $token;
    }

    public function revoke(PersonalAccessToken $token): void
    {
        $token->delete();
    }

    public function revokeAllFor(Authenticatable $tokenable): void
    {
        PersonalAccessToken::query()
            ->where('tokenable_type', $tokenable->getMorphClass())
            ->where('tokenable_id', $tokenable->getAuthIdentifier())
            ->delete(); // SOFT DELETE maybe?
    }

    private function hashToken(string $plainTextToken): string
    {
        return hash('sha256', $plainTextToken);
    }
}
