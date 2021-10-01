<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token\Api;

use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Services\ApiKeyFactory;
use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;

class CreateTest extends AbstractBaseWebTestCase
{
    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);

        $user = $testUserFactory->create();
        $apiKey = $apiKeyFactory->create('api key label', $user);

        $response = $this->application->makeApiCreateTokenRequest((string) $apiKey);

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        (new JsonResponseAsserter(200))
            ->addBodyAsserter($jwtTokenBodyAsserterFactory->create(
                'token',
                [
                    TokenInterface::CLAIM_EMAIL => $user->getUserIdentifier(),
                    TokenInterface::CLAIM_USER_ID => $user->getId(),
                    TokenInterface::CLAIM_AUDIENCE => [
                        AudienceClaimInterface::AUD_API,
                    ],
                ]
            ))
            ->assertFromSymfonyResponse($response)
        ;
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->application->makeApiCreateTokenRequest('');

        self::assertSame(401, $response->getStatusCode());
    }
}
