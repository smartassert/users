<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Routes;
use App\Tests\Integration\AbstractIntegrationTest;
use App\Tests\Services\UserRemover;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

class CreateTest extends AbstractIntegrationTest
{
    private const TEST_USER_EMAIL = 'user@example.com';
    private const TEST_USER_PASSWORD = 'user-password';

    /**
     * @dataProvider createInvalidCredentialsDataProvider
     *
     * @param array<string, string> $headers
     */
    public function testCreateNoAuthorizationHeader(array $headers): void
    {
        $request = $this->createRequest($headers);
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
        static::createClient();

        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        $request = $this->createRequest(
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
        static::createClient();
        $userRemover = self::getContainer()->get(UserRemover::class);
        \assert($userRemover instanceof UserRemover);
        $userRemover->removeAll();

        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        $request = $this->createRequest(
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => $adminToken,
            ],
            http_build_query([
                'email' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
            ])
        );

        $response = $this->httpClient->sendRequest($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $user = $userRepository->findByEmail('user@example.com');
        self::assertInstanceOf(User::class, $user);

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertSame(
            [
                'user' => [
                    'id' => $user->getId(),
                    'user-identifier' => self::TEST_USER_EMAIL,
                ],
            ],
            $responseData
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(array $headers = [], ?string $body = null): RequestInterface
    {
        $request = $this->requestFactory->createRequest('POST', Routes::ROUTE_ADMIN_USER_CREATE);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        if (is_string($body)) {
            $request = $request->withBody(Utils::streamFor($body));
        }

        return $request;
    }
}
