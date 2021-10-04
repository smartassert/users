<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\ApplicationResponseAsserter;
use Psr\Http\Message\RequestInterface;

class CreateTest extends AbstractIntegrationTest
{
    /**
     * @dataProvider createInvalidCredentialsDataProvider
     *
     * @param array<string, string> $headers
     */
    public function testCreateNoAuthorizationHeader(array $headers): void
    {
        $request = $this->createCreateUserRequest($headers);
        $response = $this->httpClient->sendRequest($request);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createInvalidCredentialsDataProvider(): array
    {
        return [
            'no credentials' => [
                'headers' => [],
            ],
            'invalid credentials' => [
                'headers' => [
                    'Authorization' => 'invalid-token',
                ],
            ],
        ];
    }

    /**
     * @dataProvider createBadRequestDataProvider
     *
     * @param array<string, string> $data
     */
    public function testCreateBadRequest(array $data): void
    {
        $this->getApplicationClient();

        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        $request = $this->createCreateUserRequest(
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => $adminToken,
            ],
            http_build_query($data)
        );

        $response = $this->httpClient->sendRequest($request);

        self::assertSame(400, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadRequestDataProvider(): array
    {
        return [
            'no data' => [
                'data' => [],
            ],
            'email missing' => [
                'data' => [
                    'password' => self::TEST_USER_PASSWORD,
                ],
            ],
            'password missing' => [
                'data' => [
                    'email' => self::TEST_USER_EMAIL,
                ],
            ],
        ];
    }

    public function testCreateSuccess(): void
    {
        $this->getApplicationClient();

        $this->removeAllUsers();

        $response = $this->createTestUser();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findByEmail('user@example.com');
        self::assertInstanceOf(User::class, $user);

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);

        $applicationResponseAsserter->assertCreateUserSuccessResponse($response, $user);
    }

    /**
     * @param array<string, string> $headers
     */
    private function createCreateUserRequest(array $headers = [], ?string $body = null): RequestInterface
    {
        return parent::createRequest('POST', Routes::ROUTE_ADMIN_USER_CREATE, $headers, $body);
    }
}
