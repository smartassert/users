<?php

declare(strict_types=1);

namespace App\Tests\Functional\RefreshToken;

use App\Tests\Services\TestUserFactory;
use App\Tests\Services\UserRemover;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CustomRefreshTest extends WebTestCase
{
    private KernelBrowser $client;

    /**
     * @var array{"userIdentifier": string, "password": string}
     */
    private array $testUserCredentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        if ($testUserFactory instanceof TestUserFactory) {
            $testUserFactory->create();
            $this->testUserCredentials = $testUserFactory->getCredentials();
        }
    }

    public function testLoginAndRefreshApiToken(): void
    {
        $this->client->request(
            method: 'POST',
            uri: '/frontend/token/create',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode(
                [
                    'username' => $this->testUserCredentials['userIdentifier'],
                    'password' => $this->testUserCredentials['password']
                ],
                \JSON_THROW_ON_ERROR
            )
        );

        $this->assertResponseIsSuccessful('Could not authenticate to the API.');

        $response = $this->client->getResponse();

        if (false === $response->getContent()) {
            $this->fail('The API did not send a proper response.');
        }

        $data = json_decode(json: $response->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('token', $data, 'JWT token is not in the response');
        $this->assertArrayHasKey('refresh_token', $data, 'Refresh token is not in the response');

        $this->client->request(
            method: 'POST',
            uri: '/api/token/refresh',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode(['refresh_token' => $data['refresh_token']], \JSON_THROW_ON_ERROR)
        );

        $this->assertResponseIsSuccessful('Could not refresh the API token.');

        $response = $this->client->getResponse();

        if (false === $response->getContent()) {
            $this->fail('The API did not send a proper response.');
        }

        $data = json_decode(json: $response->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('token', $data, 'JWT token is not in the response');
        $this->assertArrayHasKey('refresh_token', $data, 'Refresh token is not in the response');
    }
}
