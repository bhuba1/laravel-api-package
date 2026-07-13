<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Bhuba\AuthProfilePackage\Data\TokenResponse;
use Illuminate\Contracts\Auth\Authenticatable;

interface TokenServiceInterface
{
    public function issue(Authenticatable $user): TokenResponse;

    public function refresh(Authenticatable $user): TokenResponse;
}
