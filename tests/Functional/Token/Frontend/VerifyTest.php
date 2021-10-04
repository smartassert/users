<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Frontend;

use App\Tests\Functional\Token\AbstractTokenTest;
use App\Tests\Services\TestUserFactory;

class VerifyTest extends AbstractTokenTest
{
    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $response = $this->application->makeFrontendVerifyTokenRequest($jwt);

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

        $this->removeAllUsers();

        $user = $testUserFactory->create();
        $createResponse = $this->application->makeFrontendCreateTokenRequest(...$testUserFactory->getCredentials());

        $this->removeAllUsers();

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        $verifyResponse = $this->application->makeFrontendVerifyTokenRequest($createResponseData['token']);

        $this->applicationResponseAsserter->assertFrontendTokenVerifySuccessResponse($verifyResponse, $user);
    }
}
