<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use Psr\Http\Message\ResponseInterface;

class Application implements ApplicationInterface
{
    public function __construct(
        private ClientInterface $client,
        private Routes $routes,
    ) {
    }

    public function makeApiCreateTokenRequest(string $token): ResponseInterface
    {
        $headers = [
            'Authorization' => $token,
        ];

        return $this->client->makeRequest('POST', $this->routes->apiCreateTokenUrl, $headers);
    }

    public function makeApiVerifyTokenRequest(?string $jwt): ResponseInterface
    {
        return $this->makeVerifyTokenRequest($this->routes->apiVerifyTokenUrl, $jwt);
    }

    public function makeFrontendCreateTokenRequest(string $userIdentifier, string $password): ResponseInterface
    {
        return $this->client->makeRequest(
            'POST',
            $this->routes->frontendCreateTokenUrl,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'username' => $userIdentifier,
                'password' => $password,
            ])
        );
    }

    public function makeFrontendVerifyTokenRequest(?string $jwt): ResponseInterface
    {
        return $this->makeVerifyTokenRequest($this->routes->frontendVerifyTokenUrl, $jwt);
    }

    public function makeFrontendRefreshTokenRequest(string $refreshToken): ResponseInterface
    {
        return $this->client->makeRequest(
            'POST',
            $this->routes->frontendRefreshTokenUrl,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'refresh_token' => $refreshToken
            ])
        );
    }

    public function makeAdminCreateUserRequest(?string $email, ?string $password, string $adminToken): ResponseInterface
    {
        $headers = [
            'Authorization' => $adminToken,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $payload = [];
        if (is_string($email)) {
            $payload['email'] = $email;
        }

        if (is_string($password)) {
            $payload['password'] = $password;
        }

        return $this->client->makeRequest(
            'POST',
            $this->routes->adminCreateUserUrl,
            $headers,
            http_build_query($payload)
        );
    }

    public function makeAdminRevokeRefreshTokenRequest(string $userId, string $adminToken): ResponseInterface
    {
        $headers = [
            'Authorization' => $adminToken,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        return $this->client->makeRequest(
            'POST',
            $this->routes->adminRevokeRefreshTokenUrl,
            $headers,
            http_build_query([
                'id' => $userId,
            ])
        );
    }

    public function makeHealthCheckRequest(): ResponseInterface
    {
        return $this->client->makeRequest('GET', $this->routes->healthCheckUrl);
    }

    public function makeStatusRequest(): ResponseInterface
    {
        return $this->client->makeRequest('GET', $this->routes->statusUrl);
    }

    private function makeVerifyTokenRequest(string $url, ?string $jwt): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest('GET', $url, $headers);
    }
}
