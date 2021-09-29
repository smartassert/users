<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Frontend;

use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\TestUserFactory;

class VerifyTest extends AbstractBaseWebTestCase
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
        $createTokenResponse = $this->application->makeFrontendCreateTokenRequest(
            ...$testUserFactory->getCredentials()
        );

        $this->removeAllUsers();

        $createTokenResponseData = json_decode((string) $createTokenResponse->getContent(), true);
        $response = $this->application->makeFrontendVerifyTokenRequest($createTokenResponseData['token']);

        (new JsonResponseAsserter(200))
            ->addBodyAsserter(new ArrayBodyAsserter([
                'id' => $user->getId(),
                'user-identifier' => $user->getUserIdentifier(),
            ]))
            ->assert($response)
        ;
    }
}