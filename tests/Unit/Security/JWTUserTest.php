<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Exception\InvalidJwtUserPayloadException;
use App\Exception\InvalidJwtUserUsernameException;
use App\Security\JWTUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JWTUserTest extends TestCase
{
    /**
     * @param array<mixed> $payload
     */
    #[DataProvider('createFromPayloadThrowsExceptionDataProvider')]
    public function testCreateFromPayloadThrowsException(
        mixed $username,
        array $payload,
        InvalidJwtUserPayloadException|InvalidJwtUserUsernameException $expectedException
    ): void {
        self::expectExceptionObject($expectedException);

        JWTUser::createFromPayload($username, $payload);
    }

    /**
     * @return array<mixed>
     */
    public static function createFromPayloadThrowsExceptionDataProvider(): array
    {
        return [
            'username not string' => [
                'username' => true,
                'payload' => [],
                'expectedException' => new InvalidJwtUserUsernameException(true, []),
            ],
            'payload sub missing' => [
                'username' => 'user@example.com',
                'payload' => [
                    'roles' => [],
                ],
                'expectedException' => new InvalidJwtUserPayloadException(
                    'user@example.com',
                    [
                        'roles' => [],
                    ],
                    'sub'
                ),
            ],
            'payload roles missing' => [
                'username' => 'user@example.com',
                'payload' => [
                    'sub' => 'user@example.com',
                ],
                'expectedException' => new InvalidJwtUserPayloadException(
                    'user@example.com',
                    [
                        'sub' => 'user@example.com',
                    ],
                    'roles'
                ),
            ],
        ];
    }
}
