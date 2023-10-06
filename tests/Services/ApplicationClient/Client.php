<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\RouterInterface;

class Client
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly RouterInterface $router,
    ) {
    }

    public function makeRevokeAllRefreshTokensForUserRequest(
        string $userId,
        string $adminToken,
        string $method = 'POST'
    ): ResponseInterface {
        $headers = [
            'Authorization' => $adminToken,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('refreshtoken_revoke_all_for_user'),
            $headers,
            http_build_query([
                'id' => $userId,
            ])
        );
    }

    public function makeAdminCreateUserRequest(
        ?string $email,
        ?string $password,
        string $adminToken,
        string $method = 'POST'
    ): ResponseInterface {
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
            $method,
            $this->router->generate('user_create'),
            $headers,
            http_build_query($payload)
        );
    }

    public function makeCreteApiTokenRequest(string $apiKey, string $method = 'POST'): ResponseInterface
    {
        $headers = [
            'Authorization' => $apiKey,
        ];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('api_token_create'),
            $headers
        );
    }

    public function makeVerifyApiTokenRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('api_token_verify'),
            $headers
        );
    }

    public function makeRefreshFrontendTokenRequest(string $refreshToken, string $method = 'POST'): ResponseInterface
    {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('frontend_token_refresh'),
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'refresh_token' => $refreshToken
            ])
        );
    }

    public function makeCreateFrontendTokenRequest(
        string $userIdentifier,
        string $password,
        string $method = 'POST'
    ): ResponseInterface {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('frontend_token_create'),
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'username' => $userIdentifier,
                'password' => $password,
            ])
        );
    }

    public function makeListApiKeysRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('apikey_list'),
            $headers
        );
    }

    public function makeGetDefaultApkKeyRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('apikey_get_default'),
            $headers
        );
    }

    public function makeVerifyFrontendTokenRequest(?string $jwt, string $method = 'GET'): ResponseInterface
    {
        $headers = (is_string($jwt))
            ? ['Authorization' => 'Bearer ' . $jwt]
            : [];

        return $this->client->makeRequest(
            $method,
            $this->router->generate('frontend_token_verify'),
            $headers
        );
    }

    public function makeStatusRequest(): ResponseInterface
    {
        return $this->client->makeRequest('GET', $this->router->generate('status'));
    }

    public function makeHealthCheckRequest(string $method = 'GET'): ResponseInterface
    {
        return $this->client->makeRequest($method, $this->router->generate('health-check'));
    }
}
