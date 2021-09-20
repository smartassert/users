<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Admin;

use App\Security\Admin\User;
use App\Security\UserRoleInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserTest extends TestCase
{
    private const TOKEN = 'user-token';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User(self::TOKEN);
    }

    public function testIsUserInterfaceInstance(): void
    {
        self::assertInstanceOf(UserInterface::class, $this->user);
    }

    public function testGetRoles(): void
    {
        self::assertSame(
            [
                UserRoleInterface::ROLE_ADMIN,
            ],
            $this->user->getRoles()
        );
    }

    public function testGetUserIdentifier(): void
    {
        self::assertSame(self::TOKEN, $this->user->getUserIdentifier());
    }
}
