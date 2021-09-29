<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Frontend;

use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;

class CreateTest extends AbstractBaseWebTestCase
{
    public function testCreateSuccess(): void
    {
        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        $this->removeAllUsers();

        $user = $testUserFactory->create();
        $response = $this->application->makeFrontendCreateTokenRequest(...$testUserFactory->getCredentials());

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
}
