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
        $headers = $this->addHttpAuthorizationHeader([], $token);

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
        return $this->makeVerifyTokenRequest($this->apiVerifyTokenUrl, $jwt);
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
        return $this->makeVerifyTokenRequest($this->frontendVerifyTokenUrl, $jwt);
    }

    public function makeAdminCreateUserRequest(string $email, string $password, ?string $adminToken): Response
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

        return $this->client->getResponse();
    }

    private function makeVerifyTokenRequest(string $url, ?string $jwt): Response
    {
        $headers = $this->addJwtAuthorizationHeader([], $jwt);

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private function addJwtAuthorizationHeader(array $headers, ?string $jwt): array
    {
        return $this->addHttpAuthorizationHeader($headers, $jwt, 'Bearer');
    }

    /**
     * @param array<string, string> $headers
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
}
