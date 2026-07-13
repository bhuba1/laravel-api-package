<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

interface UserModelResolverInterface
{
    public function modelClass(): string;

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Model&Authenticatable>
     */
    public function query();
}
