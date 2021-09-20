<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\AdminController;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /**
     * @dataProvider createUserUnauthorizedDataProvider
     */
    public function testCreateUserUnauthorized(?string $jwt): void
    {
        $response = $this->makeCreateUserRequest($jwt);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createUserUnauthorizedDataProvider(): array
    {
        return [
            'no token' => [
                'token' => null,
            ],
            'invalid token' => [
                'token' => 'invalid-admin-token',
            ],
        ];
    }

    public function testCreateUserSuccess(): void
    {
        $primaryAdminToken = $this->getContainer()->getParameter('primary-admin-token');
        $primaryAdminToken = is_string($primaryAdminToken) ? $primaryAdminToken : '';

        $secondaryAdminToken = $this->getContainer()->getParameter('secondary-admin-token');
        $secondaryAdminToken = is_string($secondaryAdminToken) ? $secondaryAdminToken : '';

        $tokens = [$primaryAdminToken, $secondaryAdminToken];

        foreach ($tokens as $token) {
            $response = $this->makeCreateUserRequest($token);

            self::assertSame(200, $response->getStatusCode());
        }
    }

    private function makeCreateUserRequest(?string $token): Response
    {
        $headers = [];
        if (is_string($token)) {
            $headers['HTTP_AUTHORIZATION'] = $token;
        }

        $this->client->request(
            'GET',
            AdminController::ROUTE_ADMIN_USER_CREATE,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }
}
