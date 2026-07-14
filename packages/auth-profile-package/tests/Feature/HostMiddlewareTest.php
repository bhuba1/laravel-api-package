<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Feature;

use Bhuba\AuthProfilePackage\Repositories\TokenRepository;
use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class HostMiddlewareTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('auth-profile.token')->get('/api/host/me', function (Request $request) {
            return response()->json([
                'request_user_id' => $request->user()?->getAuthIdentifier(),
                'auth_user_id' => Auth::id(),
            ]);
        });
    }

    public function test_host_route_accepts_package_token_via_middleware(): void
    {
        $user = $this->createUser();
        $repository = new TokenRepository();
        $plainTextToken = Str::random(40);

        $repository->create($user, $plainTextToken, now()->addHour());

        $this->getJson('/api/host/me', $this->bearerHeaders($plainTextToken))
            ->assertOk()
            ->assertJson([
                'request_user_id' => $user->id,
                'auth_user_id' => $user->id,
            ]);
    }

    public function test_host_route_rejects_missing_bearer_token(): void
    {
        $this->getJson('/api/host/me')
            ->assertUnauthorized();
    }
}
