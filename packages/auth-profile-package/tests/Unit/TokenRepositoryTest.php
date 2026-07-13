<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Contracts\TokenRepositoryInterface;
use Bhuba\AuthProfilePackage\Models\PersonalAccessToken;
use Bhuba\AuthProfilePackage\Repositories\TokenRepository;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Bhuba\AuthProfilePackage\Tests\DatabaseTestCase;
use Illuminate\Support\Str;

class TokenRepositoryTest extends DatabaseTestCase
{
    private TokenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new TokenRepository();
    }

    public function test_create_stores_hashed_token(): void
    {
        $user = $this->createUser();
        $plainTextToken = Str::random(40);

        $this->repository->create($user, $plainTextToken, now()->addHour());

        $this->assertDatabaseMissing('auth_profile_tokens', [
            'token' => $plainTextToken,
        ]);

        $this->assertDatabaseHas('auth_profile_tokens', [
            'tokenable_type' => $user->getMorphClass(),
            'tokenable_id' => $user->getAuthIdentifier(),
            'token' => hash('sha256', $plainTextToken),
        ]);
    }

    public function test_find_valid_token_returns_token_for_valid_plain_text(): void
    {
        $user = $this->createUser();
        $plainTextToken = Str::random(40);

        $this->repository->create($user, $plainTextToken, now()->addHour());

        $found = $this->repository->findValidToken($plainTextToken);

        $this->assertInstanceOf(PersonalAccessToken::class, $found);
        $this->assertTrue($found->tokenable->is($user));
    }

    public function test_find_valid_token_returns_null_for_expired_token(): void
    {
        $user = $this->createUser();
        $plainTextToken = Str::random(40);

        $this->repository->create($user, $plainTextToken, now()->subMinute());

        $this->assertNull($this->repository->findValidToken($plainTextToken));
    }

    public function test_find_valid_token_returns_null_for_wrong_token(): void
    {
        $user = $this->createUser();

        $this->repository->create($user, Str::random(40), now()->addHour());

        $this->assertNull($this->repository->findValidToken(Str::random(40)));
    }

    public function test_revoke_deletes_token(): void
    {
        $user = $this->createUser();
        $plainTextToken = Str::random(40);

        $token = $this->repository->create($user, $plainTextToken, now()->addHour());

        $this->repository->revoke($token);

        $this->assertDatabaseMissing('auth_profile_tokens', [
            'id' => $token->id,
        ]);
    }

    public function test_revoke_all_for_deletes_only_matching_morph_tokens(): void
    {
        $firstUser = $this->createUser(['email' => 'first@example.com']);
        $secondUser = $this->createUser(['email' => 'second@example.com']);

        $this->repository->create($firstUser, Str::random(40), now()->addHour());
        $this->repository->create($firstUser, Str::random(40), now()->addHour());
        $this->repository->create($secondUser, Str::random(40), now()->addHour());

        $this->repository->revokeAllFor($firstUser);

        $this->assertSame(0, PersonalAccessToken::query()
            ->where('tokenable_type', $firstUser->getMorphClass())
            ->where('tokenable_id', $firstUser->getAuthIdentifier())
            ->count());

        $this->assertSame(1, PersonalAccessToken::query()
            ->where('tokenable_type', $secondUser->getMorphClass())
            ->where('tokenable_id', $secondUser->getAuthIdentifier())
            ->count());
    }
}
