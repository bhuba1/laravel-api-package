<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Http\Controllers;

use Bhuba\AuthProfilePackage\Contracts\ProfileServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController
{
    public function __construct(
        private readonly ProfileServiceInterface $profileService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Authenticatable) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($this->profileService->get($user));
    }
}
