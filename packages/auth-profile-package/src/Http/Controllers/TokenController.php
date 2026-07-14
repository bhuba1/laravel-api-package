<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Http\Controllers;

use Bhuba\AuthProfilePackage\Contracts\TokenServiceInterface;
use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Bhuba\AuthProfilePackage\Support\RequestAttributes;
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

        $currentToken = $request->attributes->get(RequestAttributes::ACCESS_TOKEN);

        return response()->json($tokenService->refresh(
            $user,
            $currentToken instanceof PersonalAccessToken ? $currentToken : null,
        ));
    }

    public function revoke(Request $request, TokenServiceInterface $tokenService): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Authenticatable) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentToken = $request->attributes->get(RequestAttributes::ACCESS_TOKEN);

        if (! $currentToken instanceof PersonalAccessToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $tokenService->revoke($currentToken);

        return response()->json(['message' => 'Token revoked.']);
    }
}
