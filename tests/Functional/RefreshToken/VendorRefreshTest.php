<?php

declare(strict_types=1);

namespace App\Tests\Functional\RefreshToken;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class VendorRefreshTest extends WebTestCase
{
    public function testLoginAndRefreshApiToken(): void
    {
        $client = self::createClient();

        $client->request(
            method: 'POST',
            uri: '/api/login',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode(['username' => 'user1', 'password' => 'password'], \JSON_THROW_ON_ERROR)
        );

        $this->assertResponseIsSuccessful('Could not authenticate to the API.');

        $response = $client->getResponse();

        if (false === $response->getContent()) {
            $this->fail('The API did not send a proper response.');
        }

        $data = json_decode(json: $response->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('token', $data, 'JWT token is not in the response');
        $this->assertArrayHasKey('refresh_token', $data, 'Refresh token is not in the response');

        $client->request(
            method: 'POST',
            uri: '/api/token/refresh',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode(['refresh_token' => $data['refresh_token']], \JSON_THROW_ON_ERROR)
        );

        $this->assertResponseIsSuccessful('Could not refresh the API token.');

        $response = $client->getResponse();

        if (false === $response->getContent()) {
            $this->fail('The API did not send a proper response.');
        }

        $data = json_decode(json: $response->getContent(), associative: true, flags: \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('token', $data, 'JWT token is not in the response');
        $this->assertArrayHasKey('refresh_token', $data, 'Refresh token is not in the response');
    }
}
