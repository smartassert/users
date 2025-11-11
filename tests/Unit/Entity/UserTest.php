<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @param array<mixed> $expectedSerializedUser
     */
    #[DataProvider('jsonSerializeDataProvider')]
    public function testJsonSerialize(User $user, array $expectedSerializedUser): void
    {
        self::assertSame($expectedSerializedUser, $user->jsonSerialize());
    }

    /**
     * @return array<mixed>
     */
    public static function jsonSerializeDataProvider(): array
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
