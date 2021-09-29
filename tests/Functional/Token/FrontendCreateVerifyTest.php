<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;

class FrontendCreateVerifyTest extends AbstractBaseWebTestCase
{
    private TestUserFactory $testUserFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->testUserFactory = $testUserFactory;

        $this->removeAllUsers();
    }

    public function testCreateSuccess(): void
    {
        $user = $this->testUserFactory->create();
        $response = $this->application->makeFrontendCreateTokenRequest(...$this->testUserFactory->getCredentials());

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        (new JsonResponseAsserter(200))
            ->addBodyAsserter($jwtTokenBodyAsserterFactory->create(
                'token',
                [
                    TokenInterface::CLAIM_EMAIL => $user->getUserIdentifier(),
                    TokenInterface::CLAIM_USER_ID => $user->getId(),
                    TokenInterface::CLAIM_AUDIENCE => [
                        AudienceClaimInterface::AUD_FRONTEND,
                    ],
                ]
            ))
            ->assert($response)
        ;
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->application->makeFrontendCreateTokenRequest('', '');

        self::assertSame(401, $response->getStatusCode());
    }

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
        $user = $this->testUserFactory->create();
        $createTokenResponse = $this->application->makeFrontendCreateTokenRequest(
            ...$this->testUserFactory->getCredentials()
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
