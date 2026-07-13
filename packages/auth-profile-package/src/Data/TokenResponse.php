<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Data;

use JsonSerializable;

final readonly class TokenResponse implements JsonSerializable
{
    public function __construct(
        public string $token,
        public string $expiresAt,
    ) {}

    /**
     * @return array{token: string, expires_at: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'expires_at' => $this->expiresAt,
        ];
    }
}
