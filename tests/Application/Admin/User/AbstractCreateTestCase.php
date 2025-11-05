<?php

declare(strict_types=1);

namespace App\Tests\Application\Admin\User;

use App\Repository\UserRepository;
use App\Tests\Application\AbstractApplicationTestCase;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCreateTestCase extends AbstractApplicationTestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;
    }

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeAdminCreateUserRequest(
            'identifier',
            'password',
            $this->getAdminToken(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
    {
        return [
            'GET' => [
                'method' => 'GET',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    public function testCreateUnauthorized(): void
    {
        $response = $this->applicationClient->makeAdminCreateUserRequest(
            'identifier',
            'password',
            'invalid admin token'
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    /**
     * @dataProvider createBadRequestDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testCreateBadRequest(
        ?string $identifier,
        ?string $password,
        array $expectedResponseData,
    ): void {
        $response = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));
        self::assertEquals($expectedResponseData, json_decode($response->getBody()->getContents(), true));
        self::assertSame(0, $this->userRepository->count([]));
    }

    /**
     * @return array<mixed>
     */
    public function createBadRequestDataProvider(): array
    {
        $identifierTooLong = str_repeat('.', 255);

        return [
            'identifier missing' => [
                'identifier' => null,
                'password' => 'non-empty value',
                'expectedResponseData' => [
                    'class' => 'bad_request',
                    'parameter' => [
                        'name' => 'identifier',
                        'value' => '',
                        'requirements' => [
                            'data_type' => 'string',
                            'size' => [
                                'minimum' => 1,
                                'maximum' => 254,
                            ]
                        ],
                    ],
                    'type' => 'wrong_size',
                ],
            ],
            'identifier empty' => [
                'identifier' => '',
                'password' => 'non-empty value',
                'expectedResponseData' => [
                    'class' => 'bad_request',
                    'parameter' => [
                        'name' => 'identifier',
                        'value' => '',
                        'requirements' => [
                            'data_type' => 'string',
                            'size' => [
                                'minimum' => 1,
                                'maximum' => 254,
                            ]
                        ],
                    ],
                    'type' => 'wrong_size',
                ],
            ],
            'identifier too long' => [
                'identifier' => $identifierTooLong,
                'password' => 'non-empty value',
                'expectedResponseData' => [
                    'class' => 'bad_request',
                    'parameter' => [
                        'name' => 'identifier',
                        'value' => $identifierTooLong,
                        'requirements' => [
                            'data_type' => 'string',
                            'size' => [
                                'minimum' => 1,
                                'maximum' => 254,
                            ]
                        ],
                    ],
                    'type' => 'wrong_size',
                ],
            ],
            'password missing' => [
                'identifier' => 'non-empty value',
                'password' => null,
                'expectedResponseData' => [
                    'class' => 'bad_request',
                    'parameter' => [
                        'name' => 'password',
                        'value' => '',
                        'requirements' => [
                            'data_type' => 'string',
                            'size' => [
                                'minimum' => 1,
                                'maximum' => null,
                            ]
                        ],
                    ],
                    'type' => 'wrong_size',
                ],
            ],
            'password empty' => [
                'identifier' => 'non-empty value',
                'password' => '',
                'expectedResponseData' => [
                    'class' => 'bad_request',
                    'parameter' => [
                        'name' => 'password',
                        'value' => '',
                        'requirements' => [
                            'data_type' => 'string',
                            'size' => [
                                'minimum' => 1,
                                'maximum' => null,
                            ]
                        ],
                    ],
                    'type' => 'wrong_size',
                ],
            ],
        ];
    }

    public function testCreateUserAlreadyExists(): void
    {
        $identifier = 'user@example.com';
        $password = 'password';

        $successResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(200, $successResponse->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));

        $badRequestResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(409, $badRequestResponse->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));
        $this->verifyCreateUserResponse($badRequestResponse, $identifier);
    }

    public function testCreateSuccess(): void
    {
        self::assertSame(0, $this->userRepository->count([]));

        $identifier = 'user@example.com';
        $password = 'password';

        $response = $this->applicationClient->makeAdminCreateUserRequest(
            $identifier,
            $password,
            $this->getAdminToken()
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->userRepository->count([]));

        $this->verifyCreateUserResponse($response, $identifier);
    }

    abstract protected function getAdminToken(): string;

    private function verifyCreateUserResponse(ResponseInterface $response, string $expectedUserIdentifier): void
    {
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        $user = $this->userRepository->findAll()[0];

        self::assertSame(
            [
                'id' => $user->getId(),
                'user-identifier' => $expectedUserIdentifier,
            ],
            $responseData
        );
    }
}
