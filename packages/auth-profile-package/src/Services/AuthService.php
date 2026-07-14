<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Services;

use Bhuba\AuthProfilePackage\Contracts\AuthServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Bhuba\AuthProfilePackage\Contracts\UserRepositoryInterface;
use Bhuba\AuthProfilePackage\Data\LoginCredentials;
use Bhuba\AuthProfilePackage\Data\RegisterCredentials;
use Bhuba\AuthProfilePackage\Data\TokenResponse;
use Bhuba\AuthProfilePackage\Support\RegistrationAttributes;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TokenServiceInterface $tokenService,
    ) {}

    public function register(RegisterCredentials $credentials): TokenResponse
    {
        return DB::transaction(function () use ($credentials): TokenResponse {
            $user = $this->userRepository->create(
                RegistrationAttributes::prepare($credentials->attributes),
            );

            return $this->tokenService->issue($user);
        });
    }
   
    /**
     * @param LoginCredentials $credentials
     * @return TokenResponse
     * @throws ValidationException
   */
    public function login(LoginCredentials $credentials): TokenResponse
    {
        $user = $this->userRepository->findByEmail($credentials->email);

        if (! $user instanceof Authenticatable || ! Hash::check($credentials->password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->tokenService->issue($user);
    }
}
