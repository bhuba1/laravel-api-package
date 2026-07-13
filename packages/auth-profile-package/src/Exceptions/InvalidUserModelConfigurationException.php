<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Exceptions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class InvalidUserModelConfigurationException extends \InvalidArgumentException
{
    public static function notAString(mixed $modelClass): self
    {
        return new self('The auth-profile-package.user_model config value must be a non-empty class string.');
    }

    public static function doesNotExtendModel(string $modelClass): self
    {
        return new self(sprintf('The configured user model [%s] must extend %s.', $modelClass, Model::class));
    }

    public static function doesNotImplementAuthenticatable(string $modelClass): self
    {
        return new self(sprintf('The configured user model [%s] must implement %s.', $modelClass, Authenticatable::class));
    }
}
