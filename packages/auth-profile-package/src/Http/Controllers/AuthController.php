<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Http\Controllers;

use Bhuba\AuthProfilePackage\Contracts\AuthServiceInterface;
use Bhuba\AuthProfilePackage\Data\LoginCredentials;
use Bhuba\AuthProfilePackage\Data\RegisterCredentials;
use Bhuba\AuthProfilePackage\Http\Requests\LoginRequest;
use Bhuba\AuthProfilePackage\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;

final class AuthController
{
    public function register(RegisterRequest $request, AuthServiceInterface $authService): JsonResponse
    {
        return response()->json(
            $authService->register(RegisterCredentials::fromValidated($request->validated())),
            201,
        );
    }

    public function login(LoginRequest $request, AuthServiceInterface $authService): JsonResponse
    {
        return response()->json(
            $authService->login(LoginCredentials::fromValidated($request->validated())),
        );
    }
}
