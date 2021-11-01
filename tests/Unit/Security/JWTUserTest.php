<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Exception\InvalidJwtUserPayloadException;
use App\Exception\InvalidJwtUserUsernameException;
use App\Security\JWTUser;
use PHPUnit\Framework\TestCase;

class JWTUserTest extends TestCase
{
    /**
     * @dataProvider createFromPayloadThrowsExceptionDataProvider
     *
     * @param array<mixed>                                                   $payload
     * @param InvalidJwtUserPayloadException|InvalidJwtUserUsernameException $expectedException
     */
    public function testCreateFromPayloadThrowsException(
        mixed $username,
        array $payload,
        InvalidJwtUserUsernameException|InvalidJwtUserPayloadException $expectedException
    ): void {
        self::expectExceptionObject($expectedException);

        JWTUser::createFromPayload($username, $payload);
    }

    /**
     * @return array<mixed>
     */
    public function createFromPayloadThrowsExceptionDataProvider(): array
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
