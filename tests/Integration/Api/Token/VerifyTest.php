<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Services\ApiKeyFactory;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\ApplicationResponseAsserter;
use Psr\Http\Message\RequestInterface;

class VerifyTest extends AbstractIntegrationTest
{
    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $request = $this->createVerifyTokenRequest($jwt);

        $response = $this->httpClient->sendRequest($request);

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

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $user = $userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);
        $apiKey = $apiKeyFactory->create('api key label', $user);

        $createRequest = $this->createCreateTokenRequest((string) $apiKey);
        $createResponse = $this->httpClient->sendRequest($createRequest);

        $this->removeAllUsers();

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);

        $request = $this->createVerifyTokenRequest($createResponseData['token']);
        $response = $this->httpClient->sendRequest($request);

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);

        $applicationResponseAsserter->assertApiTokenVerifySuccessResponse($response, $user);
    }

    private function createVerifyTokenRequest(?string $jwt): RequestInterface
    {
        $headers = [];
        if (is_string($jwt)) {
            $headers['Authorization'] = 'Bearer ' . $jwt;
        }

        return parent::createRequest(
            'GET',
            Routes::ROUTE_API_TOKEN_VERIFY,
            $headers
        );
    }

    private function createCreateTokenRequest(string $apiKey): RequestInterface
    {
        return parent::createRequest(
            'POST',
            Routes::ROUTE_API_TOKEN_CREATE,
            [
                'Authorization' => $apiKey,
            ]
        );
    }
}
