<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\CreateUserRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CreateUserRequestTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(Request $request, CreateUserRequest $expected): void
    {
        self::assertEquals($expected, CreateUserRequest::create($request));
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'empty' => [
                'request' => new Request(
                    [],
                    [
                        CreateUserRequest::KEY_EMAIL => '',
                    ],
                ),
                'expected' => new CreateUserRequest('', ''),
            ],
            'email empty, password missing' => [
                'request' => new Request(
                    [],
                    [
                        CreateUserRequest::KEY_EMAIL => '',
                    ],
                ),
                'expected' => new CreateUserRequest('', ''),
            ],
            'email empty, password empty' => [
                'request' => new Request(
                    [],
                    [
                        CreateUserRequest::KEY_EMAIL => '',
                        CreateUserRequest::KEY_PASSWORD => '',
                    ],
                ),
                'expected' => new CreateUserRequest('', ''),
            ],
            'email non-empty, password empty' => [
                'request' => new Request(
                    [],
                    [
                        CreateUserRequest::KEY_EMAIL => 'user@example.com',
                        CreateUserRequest::KEY_PASSWORD => '',
                    ],
                ),
                'expected' => new CreateUserRequest('user@example.com', ''),
            ],
            'email non-empty, password non-empty' => [
                'request' => new Request(
                    [],
                    [
                        CreateUserRequest::KEY_EMAIL => 'user@example.com',
                        CreateUserRequest::KEY_PASSWORD => 'password!',
                    ],
                ),
                'expected' => new CreateUserRequest('user@example.com', 'password!'),
            ],
        ];
    }
}
