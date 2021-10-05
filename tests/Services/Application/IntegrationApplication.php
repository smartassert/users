<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use App\Tests\Services\ApplicationRoutes;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class IntegrationApplication extends AbstractBaseApplication
{
    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private ClientInterface $client,
        ApplicationRoutes $routes,
    ) {
        parent::__construct($routes);
    }

    public function makeApiCreateTokenRequest(string $token): ResponseInterface
    {
        $request = $this->createRequest(
            'POST',
            $this->routes->getApiCreateTokenUrl(),
            [
                'Authorization' => $token,
            ]
        );

        return $this->client->sendRequest($request);
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

    public function makeAdminCreateUserRequest(
        ?string $email,
        ?string $password,
        ?string $adminToken
    ): ResponseInterface {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $headers = $this->addHttpAuthorizationHeader($headers, $adminToken);

        $payload = [];
        if (is_string($email)) {
            $payload['email'] = $email;
        }

        if (is_string($password)) {
            $payload['password'] = $password;
        }

        $request = $this->createRequest(
            'POST',
            $this->routes->getAdminCreateUserUrl(),
            $headers,
            http_build_query($payload)
        );

        return $this->client->sendRequest($request);
    }

    public function makeAdminRevokeRefreshTokenRequest(string $userId, string $adminToken): ResponseInterface
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $headers = $this->addHttpAuthorizationHeader($headers, $adminToken);

        $request = $this->createRequest(
            'POST',
            $this->routes->getAdminRevokeRefreshTokenUrl(),
            $headers,
            http_build_query([
                'id' => $userId,
            ])
        );

        return $this->client->sendRequest($request);
    }

    private function makeVerifyTokenRequest(string $url, ?string $jwt): ResponseInterface
    {
        $headers = $this->createJwtAuthorizationHeader($jwt);

        $request = $this->createRequest(
            'GET',
            $url,
            $headers
        );

        return $this->client->sendRequest($request);
    }

    /**
     * @return array<string, string>
     */
    private function createJwtAuthorizationHeader(?string $jwt): array
    {
        return $this->addHttpAuthorizationHeader([], $jwt, 'Bearer');
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

            $headers['Authorization'] = $value;
        }

        return $headers;
    }

    /**
     * @param array<mixed> $payload
     */
    private function makeJsonPayloadRequest(string $url, array $payload): ResponseInterface
    {
        $request = $this->createRequest(
            'POST',
            $url,
            [
                'Content-Type' => 'application/json'
            ],
            (string) json_encode($payload)
        );

        return $this->client->sendRequest($request);
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(
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
}
