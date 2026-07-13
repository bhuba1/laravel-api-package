<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Http\Requests;

use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(UserModelResolverInterface $userModelResolver): array
    {
        $modelClass = $userModelResolver->modelClass();
        $table = (new $modelClass)->getTable();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique($table, 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
