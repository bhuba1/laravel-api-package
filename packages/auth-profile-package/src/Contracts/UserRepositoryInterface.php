<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?Authenticatable;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Authenticatable;
}
