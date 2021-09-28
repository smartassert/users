<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\ApiTokenController;
use App\Entity\ApiKey;
use App\Entity\User;
use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Services\ApiKeyFactory;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\TestUserFactory;
use App\Tests\Services\UserRemover;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private User $user;
    private ApiKey $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->removeAllUsers();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->user = $testUserFactory->create();

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $this->apiKey = $apiKeyFactory->create('api key label', $this->user);
    }

    public function testCreateSuccess(): void
    {
        $response = $this->makeCreateTokenRequest((string) $this->apiKey);

        $jwtTokenBodyAsserterFactory = self::getContainer()->get(JwtTokenBodyAsserterFactory::class);
        \assert($jwtTokenBodyAsserterFactory instanceof JwtTokenBodyAsserterFactory);

        (new JsonResponseAsserter(200))
            ->addBodyAsserter($jwtTokenBodyAsserterFactory->create(
                'token',
                [
                    TokenInterface::CLAIM_USER_ID => $this->user->getId(),
                    TokenInterface::CLAIM_AUDIENCE => [
                        AudienceClaimInterface::AUD_API,
                    ],
                ],
                [
                    TokenInterface::CLAIM_EMAIL,
                ]
            ))
            ->assert($response)
        ;
    }

    public function testCreateUserDoesNotExist(): void
    {
        $response = $this->makeCreateTokenRequest('');

        self::assertSame(401, $response->getStatusCode());
    }

    private function makeCreateTokenRequest(string $token): Response
    {
        $headers = [
            'HTTP_AUTHORIZATION' => $token,
        ];

        $this->client->request(
            'POST',
            ApiTokenController::ROUTE_CREATE,
            [],
            [],
            $headers,
        );

        return $this->client->getResponse();
    }

    private function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }
    }
}
