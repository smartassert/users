<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Api;

use App\Services\ApiKeyFactory;
use App\Tests\Functional\Token\AbstractTokenTest;
use App\Tests\Services\TestUserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class VerifyTest extends AbstractTokenTest
{
    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $response = $this->application->makeApiVerifyTokenRequest($jwt);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function verifyUnauthorizedDataProvider(): array
    {
        return [
            'no jwt' => [
                'token' => null,
            ],
            'malformed jwt' => [
                'token' => 'malformed.jwt.token',
            ],
            'invalid jwt' => [
                'token' => 'eyJhbGciOiJIUzI1NiJ9.e30.ZRrHA1JJJW8opsbCGfG_HACGpVUMN_a9IV7pAx_Zmeo',
            ],
        ];
    }

    public function testVerifyValidJwt(): void
    {
        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $user = $testUserFactory->create();
        $userId = $user->getId();
        $apiKey = $apiKeyFactory->create('api key label', $user);
        $createResponse = $this->application->makeApiCreateTokenRequest((string) $apiKey);

        $this->removeAllUsers();

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
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

    /**
     * @return array<mixed>
     */
    public function verifyInvalidUserDataDataProvider(): array
    {
        return [
            'empty token data' => [
                'tokenData' => [],
                'expectedResponseStatusCode' => 401,
                'expectedResponseBodyContains' => 'Unable to find key \u0022email\u0022 in the token payload.',
            ],
            'payload sub key missing' => [
                'tokenData' => [
                    'email' => 'user@example.com',
                ],
                'expectedResponseStatusCode' => 500,
                'expectedResponseBodyContains' => 'Payload key &quot;sub&quot; invalid',
            ],
            'payload roles key missing' => [
                'tokenData' => [
                    'email' => 'user@example.com',
                    'sub' => 'user@example.com',
                ],
                'expectedResponseStatusCode' => 500,
                'expectedResponseBodyContains' => 'Payload key &quot;roles&quot; invalid',
            ],
        ];
    }
}
