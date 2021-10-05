<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use App\Request\CreateUserRequest;
use App\Request\RevokeRefreshTokenRequest;
use App\Tests\Services\ApplicationRoutes;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class FunctionalApplication extends AbstractBaseApplication
{
    private KernelBrowser $client;

    public function __construct(
        private HttpMessageFactoryInterface $httpMessageFactory,
        ApplicationRoutes $routes,
    ) {
        parent::__construct($routes);
    }

    public function setClient(KernelBrowser $client): void
    {
        $this->client = $client;
    }

    public function makeApiCreateTokenRequest(string $token): ResponseInterface
    {
        $headers = $this->addHttpAuthorizationHeader([], $token);

        $this->client->request(
            'POST',
            $this->routes->getApiCreateTokenUrl(),
            [],
            [],
            $headers,
        );

        return $this->createPsrResponse($this->client->getResponse());
    }

    public function makeApiVerifyTokenRequest(?string $jwt): ResponseInterface
    {
        return $this->makeVerifyTokenRequest($this->routes->getApiVerifyTokenUrl(), $jwt);
    }

    public function makeFrontendCreateTokenRequest(string $userIdentifier, string $password): ResponseInterface
    {
        return $this->makeJsonPayloadRequest(
            $this->routes->getFrontendCreateTokenUrl(),
            [
                'username' => $userIdentifier,
                'password' => $password,
            ]
        );
    }

    public function makeFrontendVerifyTokenRequest(?string $jwt): ResponseInterface
    {
        return $this->makeVerifyTokenRequest($this->routes->getFrontendVerifyTokenUrl(), $jwt);
    }

    public function makeFrontendRefreshTokenRequest(string $refreshToken): ResponseInterface
    {
        return $this->makeJsonPayloadRequest(
            $this->routes->getFrontendRefreshTokenUrl(),
            [
                'refresh_token' => $refreshToken
            ]
        );
    }

    public function makeAdminCreateUserRequest(string $email, string $password, ?string $adminToken): ResponseInterface
    {
        $headers = $this->addHttpAuthorizationHeader([], $adminToken);

        $this->client->request(
            'POST',
            $this->routes->getAdminCreateUserUrl(),
            [
                CreateUserRequest::KEY_EMAIL => $email,
                CreateUserRequest::KEY_PASSWORD => $password,
            ],
            [],
            $headers,
        );

        return $this->createPsrResponse($this->client->getResponse());
    }

    public function makeAdminRevokeRefreshTokenRequest(string $userId, string $adminToken): ResponseInterface
    {
        $headers = $this->addHttpAuthorizationHeader([], $adminToken);

        $this->client->request(
            'POST',
            $this->routes->getAdminRevokeRefreshTokenUrl(),
            [
                RevokeRefreshTokenRequest::KEY_ID => $userId,
            ],
            [],
            $headers,
        );

        return $this->createPsrResponse($this->client->getResponse());
    }

    private function makeVerifyTokenRequest(string $url, ?string $jwt): ResponseInterface
    {
        $headers = $this->addJwtAuthorizationHeader([], $jwt);

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            $headers,
        );

        return $this->createPsrResponse($this->client->getResponse());
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function addJwtAuthorizationHeader(array $headers, ?string $jwt): array
    {
        return $this->addHttpAuthorizationHeader($headers, $jwt, 'Bearer');
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function addHttpAuthorizationHeader(array $headers, ?string $value, ?string $prefix = null): array
    {
        if (is_string($value)) {
            if (is_string($prefix)) {
                $value = $prefix . ' ' . $value;
            }

            $headers['HTTP_AUTHORIZATION'] = $value;
        }

        return $headers;
    }

    /**
     * @param array<mixed> $payload
     */
    private function makeJsonPayloadRequest(string $url, array $payload): ResponseInterface
    {
        $this->client->request(
            'POST',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode($payload)
        );

        return $this->createPsrResponse($this->client->getResponse());
    }

    private function createPsrResponse(Response $symfonyResponse): ResponseInterface
    {
        $response = $this->httpMessageFactory->createResponse($symfonyResponse);
        $response->getBody()->rewind();

        return $response;
    }
}
