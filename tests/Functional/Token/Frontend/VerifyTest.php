<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Frontend;

use App\Tests\Functional\Token\AbstractVerifyTest;
use App\Tests\Services\TestUserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class VerifyTest extends AbstractVerifyTest
{
    /**
     * @dataProvider verifyUnauthorizedDataProvider
     */
    public function testVerifyUnauthorized(?string $jwt): void
    {
        $response = $this->application->makeFrontendVerifyTokenRequest($jwt);

        self::assertSame(401, $response->getStatusCode());
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

    /**
     * @dataProvider verifyInvalidUserDataDataProvider
     *
     * @param array<mixed> $tokenData
     */
    public function testVerifyInvalidUserData(
        array $tokenData,
        int $expectedResponseStatusCode,
        string $expectedResponseBodyContains,
    ): void {
        $encoder = self::getContainer()->get('lexik_jwt_authentication.encoder');
        \assert($encoder instanceof JWTEncoderInterface);

        $verifyResponse = $this->application->makeFrontendVerifyTokenRequest($encoder->encode($tokenData));

        self::assertSame($expectedResponseStatusCode, $verifyResponse->getStatusCode());
        self::assertStringContainsString($expectedResponseBodyContains, $verifyResponse->getBody()->getContents());
    }
}
