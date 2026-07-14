<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Bhuba\AuthProfilePackage\Data\LoginCredentials;
use Bhuba\AuthProfilePackage\Data\RegisterCredentials;
use Bhuba\AuthProfilePackage\Data\TokenResponse;
use Illuminate\Validation\ValidationException;

interface AuthServiceInterface
{
    /**
     * @param RegisterCredentials $credentials
     * @return TokenResponse
     */
    public function register(RegisterCredentials $credentials): TokenResponse;

    /**
     * @param LoginCredentials $credentials
     * @throws ValidationException
     * @return TokenResponse
     */
    public function login(LoginCredentials $credentials): TokenResponse;
}
