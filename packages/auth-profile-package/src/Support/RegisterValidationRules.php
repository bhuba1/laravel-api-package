<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Support;

use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;
use Illuminate\Validation\Rule;

final class RegisterValidationRules
{
    public function __construct(
        private readonly UserModelResolverInterface $userModelResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $fields = $this->configuredFields();
        $overrides = $this->configuredOverrides();
        $modelClass = $this->userModelResolver->modelClass();
        $table = (new $modelClass)->getTable();
        $rules = [];

        foreach ($fields as $field) {
            if (isset($overrides[$field])) {
                $rules[$field] = $overrides[$field];

                continue;
            }

            $rules[$field] = match ($field) {
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique($table, 'email')],
                'password' => ['required', 'string', 'min:8'],
                'name' => ['required', 'string', 'max:255'],
                default => ['required', 'string', 'max:255'],
            };
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    public function configuredFields(): array
    {
        $fields = config('auth-profile-package.register_fields', ['name', 'email', 'password']);

        if (! is_array($fields)) {
            return ['name', 'email', 'password'];
        }

        $normalized = [];

        foreach ($fields as $field) {
            if (is_string($field) && $field !== '') {
                $normalized[] = $field;
            }
        }

        return $normalized !== [] ? $normalized : ['name', 'email', 'password'];
    }

    /**
     * @return array<string, mixed>
     */
    private function configuredOverrides(): array
    {
        $overrides = config('auth-profile-package.register_field_rules', []);

        return is_array($overrides) ? $overrides : [];
    }
}
