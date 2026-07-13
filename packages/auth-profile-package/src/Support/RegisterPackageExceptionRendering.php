<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Support;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegisterPackageExceptionRendering
{
    public static function register(Handler $handler): void
    {
        $handler->renderable(static function (
            ThrottleRequestsException $exception,
            Request $request,
        ): ?JsonResponse {
            if (! self::isPackageRoute($request)) {
                return null;
            }

            return response()->json(
                ['message' => 'Too many requests. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS,
                $exception->getHeaders(),
            );
        });
    }

    private static function isPackageRoute(Request $request): bool
    {
        $prefix = trim((string) config('auth-profile-package.route_prefix', 'api/auth-profile'), '/');

        return $request->is($prefix) || $request->is($prefix.'/*');
    }
}
