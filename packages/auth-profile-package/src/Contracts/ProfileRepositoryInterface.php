<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface ProfileRepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildProfileArray(Authenticatable $user): array;
}
