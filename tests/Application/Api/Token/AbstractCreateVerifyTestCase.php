<?php

declare(strict_types=1);

namespace App\Tests\Application\Api\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\ApiKeyFactory;
use App\Tests\Application\AbstractApplicationTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

abstract class AbstractCreateVerifyTestCase extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreteApiTokenRequest($this->getAdminToken(), $method);

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

    /**
     * @dataProvider verifyBadMethodDataProvider
     */
    public function testVerifyBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeVerifyApiTokenRequest($this->getAdminToken(), $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function verifyBadMethodDataProvider(): array
    {
        return [
            'POST' => [
                'method' => 'POST',
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
        $response = $this->applicationClient->makeCreteApiTokenRequest('invalid api key');

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    public function testVerifyUnauthorized(): void
    {
        $response = $this->applicationClient->makeVerifyApiTokenRequest('invalid api key');

        self::assertSame(401, $response->getStatusCode());
    }

    public function testVerifyUnauthorizedForValidFrontendToken(): void
    {
        $userEmail = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userEmail,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $createFrontendTokenResponse = $this->applicationClient->makeCreateFrontendTokenRequest(
            $userEmail,
            $userPassword
        );
        self::assertSame(200, $createFrontendTokenResponse->getStatusCode());
        self::assertSame('application/json', $createFrontendTokenResponse->getHeaderLine('content-type'));

        $createFrontendTokenData = json_decode($createFrontendTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createFrontendTokenData);
        self::assertArrayHasKey('token', $createFrontendTokenData);

        $frontendToken = $createFrontendTokenData['token'];
        self::assertIsString($frontendToken);

        $verifyFrontendTokenResponse = $this->applicationClient->makeVerifyFrontendTokenRequest($frontendToken);
        self::assertSame(200, $verifyFrontendTokenResponse->getStatusCode());

        $verifyResponse = $this->applicationClient->makeVerifyApiTokenRequest($frontendToken);

        self::assertSame(401, $verifyResponse->getStatusCode());
    }

    public function testCreateAndVerifySuccess(): void
    {
        $userEmail = 'user@example.com';
        $userPassword = 'password';

        $createUserResponse = $this->applicationClient->makeAdminCreateUserRequest(
            $userEmail,
            $userPassword,
            $this->getAdminToken()
        );
        self::assertSame(200, $createUserResponse->getStatusCode());

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findAll()[0];
        self::assertInstanceOf(User::class, $user);

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $apiKey = $apiKeyFactory->create($user);
        self::assertIsString($apiKey->id);

        $createResponse = $this->applicationClient->makeCreteApiTokenRequest($apiKey->id);
        self::assertSame(200, $createResponse->getStatusCode());
        self::assertSame('application/json', $createResponse->getHeaderLine('content-type'));

        $createData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createData);
        self::assertArrayHasKey('token', $createData);

        $token = $createData['token'];
        self::assertIsString($token);

        $tokenManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        \assert($tokenManager instanceof JWTTokenManagerInterface);

        $tokenData = $tokenManager->parse($token);
        self::assertIsArray($tokenData);
        self::assertSame($user->getId(), $tokenData['sub']);
        self::assertSame($user->getUserIdentifier(), $tokenData['email']);
        self::assertSame(['api'], $tokenData['aud']);
        self::assertSame(['ROLE_USER'], $tokenData['roles']);

        $verifyResponse = $this->applicationClient->makeVerifyApiTokenRequest($token);
        self::assertSame(200, $verifyResponse->getStatusCode());
        self::assertSame('application/json', $createResponse->getHeaderLine('content-type'));

        $verifyData = json_decode($verifyResponse->getBody()->getContents(), true);
        self::assertIsArray($verifyData);
        self::assertEquals(
            [
                'id' => $user->getId(),
                'user-identifier' => $user->getUserIdentifier(),
            ],
            $verifyData
        );
    }

    abstract protected function getAdminToken(): string;
}
