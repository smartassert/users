<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\Token;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
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

        $user = $userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);

        $createRequest = $this->createCreateTokenRequest([
            'username' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $createResponse = $this->httpClient->sendRequest($createRequest);

        $this->removeAllUsers();

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);

        $request = $this->createVerifyTokenRequest($createResponseData['token']);
        $response = $this->httpClient->sendRequest($request);

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);

        $applicationResponseAsserter->assertFrontendTokenVerifySuccessResponse($response, $user);
    }

    private function createVerifyTokenRequest(?string $jwt): RequestInterface
    {
        $headers = [];
        if (is_string($jwt)) {
            $headers['Authorization'] = 'Bearer ' . $jwt;
        }

        return parent::createRequest(
            'GET',
            Routes::ROUTE_FRONTEND_TOKEN_VERIFY,
            $headers
        );
    }

    /**
     * @param array<string, string> $payload
     */
    private function createCreateTokenRequest(array $payload): RequestInterface
    {
        return parent::createRequest(
            'POST',
            Routes::ROUTE_FRONTEND_TOKEN_CREATE,
            ['Content-Type' => 'application/json'],
            0 < count($payload) ? (string) json_encode($payload) : null
        );
    }
}
