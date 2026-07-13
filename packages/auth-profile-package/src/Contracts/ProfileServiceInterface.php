<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface ProfileServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function get(Authenticatable $user): array;
}
