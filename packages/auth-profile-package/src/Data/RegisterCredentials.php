<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Data;

final readonly class RegisterCredentials
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public array $attributes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $fields = config('auth-profile-package.register_fields', ['name', 'email', 'password']);

        if (! is_array($fields)) {
            $fields = ['name', 'email', 'password'];
        }

        $attributes = [];

        foreach ($fields as $field) {
            if (is_string($field) && $field !== '' && array_key_exists($field, $validated)) {
                $attributes[$field] = $validated[$field];
            }
        }

        return new self($attributes);
    }
}
