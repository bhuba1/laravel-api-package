<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Exceptions\InvalidUserModelConfigurationException;
use Bhuba\AuthProfilePackage\Support\UserModelResolver;
use Bhuba\AuthProfilePackage\Tests\Fixtures\User;
use Bhuba\AuthProfilePackage\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class UserModelResolverTest extends TestCase
{
    private UserModelResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new UserModelResolver();
    }

    public function test_model_class_returns_configured_user_model(): void
    {
        config(['auth-profile-package.user_model' => User::class]);

        $this->assertSame(User::class, $this->resolver->modelClass());
    }

    public function test_model_class_throws_when_config_is_not_a_non_empty_string(): void
    {
        config(['auth-profile-package.user_model' => null]);

        $this->expectException(InvalidUserModelConfigurationException::class);
        $this->expectExceptionMessage('The auth-profile-package.user_model config value must be a non-empty class string.');

        $this->resolver->modelClass();
    }

    public function test_model_class_throws_when_configured_class_does_not_extend_model(): void
    {
        config(['auth-profile-package.user_model' => NotAModel::class]);

        $this->expectException(InvalidUserModelConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'The configured user model [%s] must extend %s.',
            NotAModel::class,
            Model::class,
        ));

        $this->resolver->modelClass();
    }

    public function test_model_class_throws_when_configured_class_does_not_implement_authenticatable(): void
    {
        config(['auth-profile-package.user_model' => NonAuthenticatableModel::class]);

        $this->expectException(InvalidUserModelConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'The configured user model [%s] must implement %s.',
            NonAuthenticatableModel::class,
            Authenticatable::class,
        ));

        $this->resolver->modelClass();
    }
}

final class NotAModel {}

final class NonAuthenticatableModel extends Model {}
