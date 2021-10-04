<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Api;

use App\Services\ApiKeyFactory;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\TextPlainBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\TextPlainResponseAsserter;
use App\Tests\Services\TestUserFactory;

class VerifyTest extends AbstractBaseWebTestCase
{
    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $response = $this->application->makeApiVerifyTokenRequest($jwt);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function verifyUnauthorizedDataProvider(): array
    {
        return [
            'no jwt' => [
                'token' => null,
            ],
            'malformed jwt' => [
                'token' => 'malformed.jwt.token',
            ],
            'invalid jwt' => [
                'token' => 'eyJhbGciOiJIUzI1NiJ9.e30.ZRrHA1JJJW8opsbCGfG_HACGpVUMN_a9IV7pAx_Zmeo',
            ],
        ];
    }

    public function testVerifyValidJwt(): void
    {
        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $user = $testUserFactory->create();
        $userId = $user->getId();
        $apiKey = $apiKeyFactory->create('api key label', $user);
        $createResponse = $this->application->makeApiCreateTokenRequest((string) $apiKey);

        $this->removeAllUsers();

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        $verifyResponse = $this->application->makeApiVerifyTokenRequest($createResponseData['token']);

        (new TextPlainResponseAsserter(200))
            ->addBodyAsserter(new TextPlainBodyAsserter($userId))
            ->assert($verifyResponse)
        ;
    }
}
