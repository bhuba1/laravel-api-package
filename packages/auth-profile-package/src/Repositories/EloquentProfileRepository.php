<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Repositories;

use Bhuba\AuthProfilePackage\Contracts\ProfileRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;

final class EloquentProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildFor(Authenticatable $user): array
    {
        $profile = [];

        foreach ($this->configuredFields() as $field) {
            if ($field === 'id') {
                $profile['id'] = $user->getAuthIdentifier();

                continue;
            }

            $profile[$field] = $user->{$field} ?? null;
        }

        return $profile;
    }

    /**
     * @return list<string>
     */
    private function configuredFields(): array
    {
        $fields = config('auth-profile-package.profile_fields', ['id', 'name', 'email']);

        if (! is_array($fields)) {
            return ['id', 'name', 'email'];
        }

        $normalized = [];

        foreach ($fields as $field) {
            if (is_string($field) && $field !== '') {
                $normalized[] = $field;
            }
        }

        return $normalized !== [] ? $normalized : ['id', 'name', 'email'];
    }
}
