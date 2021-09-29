<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Request\CreateUserRequest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class Application
{
    private KernelBrowser $client;

    public function __construct(
        private string $apiCreateTokenUrl,
        private string $apiVerifyTokenUrl,
        private string $frontendCreateTokenUrl,
        private string $frontendVerifyTokenUrl,
        private string $adminCreateUserUrl,
    ) {
    }

    public function setClient(KernelBrowser $client): void
    {
        $this->client = $client;
    }

    public function makeApiCreateTokenRequest(string $token): Response
    {
        $headers = [
            'HTTP_AUTHORIZATION' => $token,
        ];

        $this->client->request(
            'POST',
            $this->apiCreateTokenUrl,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }

    public function makeApiVerifyTokenRequest(?string $jwt): Response
    {
        $headers = [];
        if (is_string($jwt)) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $jwt;
        }

        $this->client->request(
            'GET',
            $this->apiVerifyTokenUrl,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }

    public function makeFrontendCreateTokenRequest(string $userIdentifier, string $password): Response
    {
        $this->client->request(
            'POST',
            $this->frontendCreateTokenUrl,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'username' => $userIdentifier,
                'password' => $password,
            ])
        );

        return $this->client->getResponse();
    }

    public function makeFrontendVerifyTokenRequest(?string $jwt): Response
    {
        $headers = [];
        if (is_string($jwt)) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $jwt;
        }

        $this->client->request(
            'GET',
            $this->frontendVerifyTokenUrl,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }

    public function makeAdminCreateUserRequest(string $email, string $password, ?string $adminToken): Response
    {
        $headers = [];
        if (is_string($adminToken)) {
            $headers['HTTP_AUTHORIZATION'] = $adminToken;
        }

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

        return $this->client->getResponse();
    }
}
