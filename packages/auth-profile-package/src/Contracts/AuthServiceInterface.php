<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Contracts;

use Bhuba\AuthProfilePackage\Data\LoginCredentials;
use Bhuba\AuthProfilePackage\Data\RegisterCredentials;
use Bhuba\AuthProfilePackage\Data\TokenResponse;

interface AuthServiceInterface
{
    public function register(RegisterCredentials $credentials): TokenResponse;

    public function login(LoginCredentials $credentials): TokenResponse;
}
