<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api\Token;

use App\Tests\Integration\AbstractIntegrationTest;

class VerifyTest extends AbstractIntegrationTest
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
        $this->removeAllUsers();
        $this->removeAllRefreshTokens();
        $this->createTestUser();

        $user = $this->getTestUser();
        $apiKey = $this->apiKeyFactory->create('api key label', $user);

        $createResponse = $this->application->makeApiCreateTokenRequest($apiKey->getId());
        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createResponseData);
        self::assertArrayHasKey('token', $createResponseData);

        $this->removeAllUsers();

        $response = $this->application->makeApiVerifyTokenRequest($createResponseData['token']);

        $this->applicationResponseAsserter->assertApiTokenVerifySuccessResponse($response, $user);
    }
}
