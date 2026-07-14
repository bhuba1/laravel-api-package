<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Support;

use Illuminate\Support\Facades\Hash;

final class RegistrationAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function prepare(array $attributes): array
    {
        $passwordFields = config('auth-profile-package.register_password_fields', ['password']);

        if (! is_array($passwordFields)) {
            $passwordFields = ['password'];
        }

        foreach ($passwordFields as $field) {
            if (! is_string($field) || $field === '') {
                continue;
            }

            if (! array_key_exists($field, $attributes)) {
                continue;
            }

            $value = $attributes[$field];

            if (! is_string($value) || $value === '') {
                continue;
            }

            $attributes[$field] = Hash::make($value);
        }

        return $attributes;
    }
}
