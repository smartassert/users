<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\CreateUserRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CreateUserRequestTest extends TestCase
{
    /**
     * @dataProvider getEmailDataProvider
     */
    public function testGetEmail(CreateUserRequest $request, string $expectedEmail): void
    {
        self::assertSame($expectedEmail, $request->getEmail());
    }

    /**
     * @return array<mixed>
     */
    public function getEmailDataProvider(): array
    {
        return [
            'no email present' => [
                'request' => new CreateUserRequest(
                    new Request()
                ),
                'expectedEmail' => '',
            ],
            'empty email present' => [
                'request' => new CreateUserRequest(
                    new Request(
                        [],
                        [
                            CreateUserRequest::KEY_EMAIL => '',
                        ],
                    )
                ),
                'expectedEmail' => '',
            ],
            'email present' => [
                'request' => new CreateUserRequest(
                    new Request(
                        [],
                        [
                            CreateUserRequest::KEY_EMAIL => 'user@example.com',
                        ],
                    )
                ),
                'expectedEmail' => 'user@example.com',
            ],
        ];
    }

    /**
     * @dataProvider getPasswordDataProvider
     */
    public function testGetPassword(CreateUserRequest $request, string $expectedPassword): void
    {
        self::assertSame($expectedPassword, $request->getPassword());
    }

    /**
     * @return array<mixed>
     */
    public function getPasswordDataProvider(): array
    {
        return [
            'no password present' => [
                'request' => new CreateUserRequest(
                    new Request()
                ),
                'expectedEmail' => '',
            ],
            'empty password present' => [
                'request' => new CreateUserRequest(
                    new Request(
                        [],
                        [
                            CreateUserRequest::KEY_PASSWORD => '',
                        ],
                    )
                ),
                'expectedEmail' => '',
            ],
            'password present' => [
                'request' => new CreateUserRequest(
                    new Request(
                        [],
                        [
                            CreateUserRequest::KEY_PASSWORD => 'password',
                        ],
                    )
                ),
                'expectedEmail' => 'password',
            ],
        ];
    }
}
