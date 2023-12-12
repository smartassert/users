<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @dataProvider jsonSerializeDataProvider
     *
     * @param array<mixed> $expectedSerializedUser
     */
    public function testJsonSerialize(User $user, array $expectedSerializedUser): void
    {
        self::assertSame($expectedSerializedUser, $user->jsonSerialize());
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerializeDataProvider(): array
    {
        return [
            'default' => [
                'user' => new User(
                    'id-value',
                    'identifier-value',
                    'password-value'
                ),
                'expectedSerializedUser' => [
                    'id' => 'id-value',
                    'user-identifier' => 'identifier-value',
                ],
            ],
        ];
    }
}
