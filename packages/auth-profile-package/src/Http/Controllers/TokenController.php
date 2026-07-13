<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Http\Controllers;

use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TokenController
{
    public function refresh(Request $request, TokenServiceInterface $tokenService): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Authenticatable) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($tokenService->refresh($user));
    }
}
