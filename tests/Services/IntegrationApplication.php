<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Request\CreateUserRequest;
use App\Request\RevokeRefreshTokenRequest;
use App\Routes;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class IntegrationApplication
{
    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private ClientInterface $client,
        private string $apiCreateTokenUrl,
        private string $apiVerifyTokenUrl,
        private string $frontendCreateTokenUrl,
        private string $frontendVerifyTokenUrl,
        private string $frontendRefreshTokenUrl,
        private string $adminCreateUserUrl,
        private string $adminRevokeRefreshTokenUrl,
    ) {
    }

    public function makeApiCreateTokenRequest(string $token): ResponseInterface
    {
        $request = $this->createRequest(
            'POST',
            Routes::ROUTE_API_TOKEN_CREATE,
            [
                'Authorization' => $token,
            ]
        );

        return $this->client->sendRequest($request);
    }

    public function makeApiVerifyTokenRequest(?string $jwt): ResponseInterface
    {
        return $this->makeVerifyTokenRequest($this->apiVerifyTokenUrl, $jwt);
    }

    public function makeFrontendCreateTokenRequest(string $userIdentifier, string $password): ResponseInterface
    {
        return $this->makeJsonPayloadRequest(
            $this->frontendCreateTokenUrl,
            [
                'username' => $userIdentifier,
                'password' => $password,
            ]
        );
    }

    public function makeFrontendVerifyTokenRequest(?string $jwt): ResponseInterface
    {
        return $this->makeVerifyTokenRequest($this->frontendVerifyTokenUrl, $jwt);
    }

    public function makeFrontendRefreshTokenRequest(string $refreshToken): ResponseInterface
    {
        return $this->makeJsonPayloadRequest(
            $this->frontendRefreshTokenUrl,
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
            $this->adminCreateUserUrl,
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
            $this->adminRevokeRefreshTokenUrl,
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
