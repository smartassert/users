<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Admin;

use App\Security\Admin\User;
use App\Security\Admin\UserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderTest extends TestCase
{
    private const TOKEN = 'user-token';

    private UserProvider $userProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = new UserProvider();
    }

    public function testLoadByUserIdentifier(): void
    {
        $user = $this->userProvider->loadUserByIdentifier(self::TOKEN);

        self::assertEquals(new User(self::TOKEN), $user);
    }

    public function testLoadByUsername(): void
    {
        $user = $this->userProvider->loadUserByUsername(self::TOKEN);

        self::assertEquals(new User(self::TOKEN), $user);
    }

    public function testSupportsClass(): void
    {
        self::assertTrue($this->userProvider->supportsClass(User::class));
        self::assertFalse($this->userProvider->supportsClass(UserInterface::class));
    }
}
