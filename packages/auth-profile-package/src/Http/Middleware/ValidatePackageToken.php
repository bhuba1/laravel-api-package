<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Http\Middleware;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ValidatePackageToken
{
    public function __construct(
        private readonly TokenRepositoryInterface $tokenRepository,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();

        if ($plainTextToken === null || $plainTextToken === '') {
            return $this->unauthenticated();
        }

        $accessToken = $this->tokenRepository->findValidToken($plainTextToken);

        if ($accessToken === null) {
            return $this->unauthenticated();
        }

        $tokenable = $accessToken->tokenable;

        if ($tokenable === null) {
            return $this->unauthenticated();
        }

        $request->setUserResolver(static fn () => $tokenable);

        return $next($request);
    }

    private function unauthenticated(): Response
    {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
