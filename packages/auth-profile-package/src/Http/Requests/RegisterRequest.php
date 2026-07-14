<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Http\Requests;

use Bhuba\AuthProfilePackage\Support\RegisterValidationRules;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(RegisterValidationRules $registerValidationRules): array
    {
        return $registerValidationRules->build();
    }
}
