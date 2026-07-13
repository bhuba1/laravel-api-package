<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Support;

use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;
use Bhuba\AuthProfilePackage\Exceptions\InvalidUserModelConfigurationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class UserModelResolver implements UserModelResolverInterface
{
    public function modelClass(): string
    {
        $modelClass = config('auth-profile-package.user_model');

        if (! is_string($modelClass) || $modelClass === '') {
            throw InvalidUserModelConfigurationException::notAString($modelClass);
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw InvalidUserModelConfigurationException::doesNotExtendModel($modelClass);
        }

        if (! is_subclass_of($modelClass, Authenticatable::class)) {
            throw InvalidUserModelConfigurationException::doesNotImplementAuthenticatable($modelClass);
        }

        return $modelClass;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Model&Authenticatable>
     */
    public function query()
    {
        /** @var class-string<Model&Authenticatable> $modelClass */
        $modelClass = $this->modelClass();

        return $modelClass::query();
    }
}
