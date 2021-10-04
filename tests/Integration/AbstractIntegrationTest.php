<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Routes;
use App\Tests\Services\UserRemover;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractIntegrationTest extends WebTestCase
{
    protected const TEST_USER_EMAIL = 'user@example.com';
    protected const TEST_USER_PASSWORD = 'user-password';

    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;
    protected KernelBrowser $applicationClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicationClient = static::createClient();

        $httpClient = self::getContainer()->get('app.tests.integration.http.client');
        \assert($httpClient instanceof ClientInterface);
        $this->httpClient = $httpClient;

        $this->requestFactory = new HttpFactory();
    }

    /**
     * @param array<string, string> $headers
     */
    protected function createRequest(
        string $method,
        string $uri,
        array $headers = [],
        ?string $body = null
    ): RequestInterface {
        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        if (is_string($body)) {
            $request = $request->withBody(Utils::streamFor($body));
        }

        return $request;
    }

    protected function createTestUser(): ResponseInterface
    {
        $request = $this->createRequest(
            'POST',
            Routes::ROUTE_ADMIN_USER_CREATE,
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => $this->getAdminToken(),
            ],
            http_build_query([
                'email' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
            ])
        );

        return $this->httpClient->sendRequest($request);
    }

    protected function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        \assert($userRemover instanceof UserRemover);
        $userRemover->removeAll();
    }

    protected function removeAllRefreshTokens(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $refreshTokenRepository = $entityManager->getRepository(RefreshToken::class);
        \assert($refreshTokenRepository instanceof RefreshTokenRepository);

        $refreshTokens = $refreshTokenRepository->findAll();

        foreach ($refreshTokens as $refreshToken) {
            $entityManager->remove($refreshToken);
            $entityManager->flush();
        }
    }

    protected function getAdminToken(): string
    {
        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        return $adminToken;
    }
}
