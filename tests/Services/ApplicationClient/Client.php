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

    public function makeAdminRevokeRefreshTokenRequest(
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
            $this->router->generate('admin_frontend_refreshtoken_revoke'),
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
            $this->router->generate('admin_user_create'),
            $headers,
            http_build_query($payload)
        );
    }
}
