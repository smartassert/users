<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Api;

use App\Services\ApiKeyFactory;
use App\Tests\Functional\Token\AbstractVerifyTest;
use App\Tests\Services\TestUserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class VerifyTest extends AbstractVerifyTest
{
    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $response = $this->application->makeApiVerifyTokenRequest($jwt);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testVerifyValidJwt(): void
    {
        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $user = $testUserFactory->create();
        $apiKey = $apiKeyFactory->create('api key label', $user);
        $createResponse = $this->application->makeApiCreateTokenRequest((string) $apiKey);

        $this->removeAllUsers();

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createResponseData);
        self::assertArrayHasKey('token', $createResponseData);

        $verifyResponse = $this->application->makeApiVerifyTokenRequest($createResponseData['token']);

        $this->applicationResponseAsserter->assertApiTokenVerifySuccessResponse($verifyResponse, $user);
    }

    /**
     * @dataProvider verifyInvalidUserDataDataProvider
     *
     * @param array<mixed> $tokenData
     */
    public function testVerifyInvalidUserData(
        array $tokenData,
        int $expectedResponseStatusCode,
        string $expectedResponseBodyContains,
    ): void {
        $encoder = self::getContainer()->get('lexik_jwt_authentication.encoder');
        \assert($encoder instanceof JWTEncoderInterface);

        $verifyResponse = $this->application->makeApiVerifyTokenRequest($encoder->encode($tokenData));

        self::assertSame($expectedResponseStatusCode, $verifyResponse->getStatusCode());
        self::assertStringContainsString($expectedResponseBodyContains, $verifyResponse->getBody()->getContents());
    }
}
