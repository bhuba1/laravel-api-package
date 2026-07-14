<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Repositories;

use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;
use Bhuba\AuthProfilePackage\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserModelResolverInterface $userModelResolver,
    ) {}

    /**
     * Finds a user by email.
     *
     * @param string $email
     * @return Authenticatable|null
     */
    public function findByEmail(string $email): ?Authenticatable
    {
        return $this->userModelResolver->query()->where('email', $email)->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return Authenticatable
     */
    public function create(array $attributes): Authenticatable
    {
        return $this->userModelResolver->query()->create($attributes);
    }
}
