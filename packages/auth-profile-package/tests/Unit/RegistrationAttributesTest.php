<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Tests\Unit;

use Bhuba\AuthProfilePackage\Support\RegistrationAttributes;
use Bhuba\AuthProfilePackage\Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class RegistrationAttributesTest extends TestCase
{
    public function test_prepare_hashes_configured_password_fields(): void
    {
        config(['auth-profile-package.register_password_fields' => ['password']]);

        $prepared = RegistrationAttributes::prepare([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue(Hash::check('password123', (string) $prepared['password']));
    }

    public function test_prepare_hashes_custom_password_field_name(): void
    {
        config(['auth-profile-package.register_password_fields' => ['secret']]);

        $prepared = RegistrationAttributes::prepare([
            'email' => 'jane@example.com',
            'secret' => 'password123',
        ]);

        $this->assertTrue(Hash::check('password123', (string) $prepared['secret']));
    }
}
