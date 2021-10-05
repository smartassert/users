<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use Psr\Http\Message\ResponseInterface;

class IntegrationApplication extends AbstractBaseApplication
{
    public function makeApiCreateTokenRequest(string $token): ResponseInterface
    {
        $headers = [
            'Authorization' => $token,
        ];

        return $this->client->makeRequest('POST', $this->routes->getApiCreateTokenUrl(), $headers);
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

        return $this->client->makeRequest(
            'POST',
            $this->routes->getAdminCreateUserUrl(),
            $headers,
            http_build_query($payload)
        );
    }

    public function makeAdminRevokeRefreshTokenRequest(string $userId, string $adminToken): ResponseInterface
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $headers = $this->addHttpAuthorizationHeader($headers, $adminToken);

        return $this->client->makeRequest(
            'POST',
            $this->routes->getAdminRevokeRefreshTokenUrl(),
            $headers,
            http_build_query([
                'id' => $userId,
            ])
        );
    }

    private function makeVerifyTokenRequest(string $url, ?string $jwt): ResponseInterface
    {
        $headers = $this->createJwtAuthorizationHeader($jwt);

        return $this->client->makeRequest('GET', $url, $headers);
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
        return $this->client->makeRequest(
            'POST',
            $url,
            [
                'Content-Type' => 'application/json'
            ],
            (string) json_encode($payload)
        );
    }
}
