<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Contracts\UserModelResolverInterface;
use Bhuba\AuthProfilePackage\Support\RegisterValidationRules;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Bhuba\AuthProfilePackage\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class RegisterValidationRulesTest extends TestCase
{
    public function test_build_returns_default_rules_for_standard_fields(): void
    {
        config([
            'auth-profile-package.register_fields' => ['name', 'email', 'password'],
            'auth-profile-package.register_field_rules' => [],
        ]);

        $rules = $this->makeRules()->build();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('min:8', $rules['password']);
    }

    public function test_build_uses_field_rule_overrides(): void
    {
        config([
            'auth-profile-package.register_fields' => ['username', 'email', 'password'],
            'auth-profile-package.register_field_rules' => [
                'username' => ['required', 'string', 'max:50', 'alpha_dash'],
            ],
        ]);

        $rules = $this->makeRules()->build();

        $this->assertSame(['required', 'string', 'max:50', 'alpha_dash'], $rules['username']);
        $this->assertContains('email', $rules['email']);
    }

    public function test_configured_fields_falls_back_to_defaults_for_invalid_config(): void
    {
        config(['auth-profile-package.register_fields' => 'invalid']);

        $this->assertSame(['name', 'email', 'password'], $this->makeRules()->configuredFields());
    }

    private function makeRules(): RegisterValidationRules
    {
        $resolver = $this->mockUserModelResolver();

        return new RegisterValidationRules($resolver);
    }

    /**
     * @return MockInterface&UserModelResolverInterface
     */
    private function mockUserModelResolver(): MockInterface
    {
        $resolver = Mockery::mock(UserModelResolverInterface::class);
        $resolver->shouldReceive('modelClass')->andReturn(User::class);

        return $resolver;
    }
}
