<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\User;
use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface;
use App\Tests\Services\Asserter\ResponseAsserter\ArrayBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JsonResponseAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\JwtTokenBodyAsserterFactory;
use App\Tests\Services\Asserter\ResponseAsserter\TextPlainBodyAsserter;
use App\Tests\Services\Asserter\ResponseAsserter\TextPlainResponseAsserter;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ApplicationResponseAsserter
{
    public function __construct(
        private JwtTokenBodyAsserterFactory $jwtTokenBodyAsserterFactory,
    ) {
    }

    public function assertCreateUserUserAlreadyExistsResponse(ResponseInterface $response, User $expectedUser): void
    {
        (new JsonResponseAsserter(Response::HTTP_BAD_REQUEST))
            ->addBodyAsserter(new ArrayBodyAsserter([
                'message' => 'User already exists',
                'user' => $expectedUser->jsonSerialize()
            ]))
            ->assert($response)
        ;
    }

    public function assertCreateUserSuccessResponse(ResponseInterface $response, User $expectedUser): void
    {
        (new JsonResponseAsserter(Response::HTTP_OK))
            ->addBodyAsserter(new ArrayBodyAsserter([
                'user' => $expectedUser->jsonSerialize(),
            ]))
            ->assert($response)
        ;
    }

    public function assertApiTokenCreateSuccessResponse(ResponseInterface $response, User $expectedUser): void
    {
        (new JsonResponseAsserter(200))
            ->addBodyAsserter($this->jwtTokenBodyAsserterFactory->create(
                'token',
                [
                    TokenInterface::CLAIM_EMAIL => $expectedUser->getUserIdentifier(),
                    TokenInterface::CLAIM_USER_ID => $expectedUser->getId(),
                    TokenInterface::CLAIM_AUDIENCE => [
                        AudienceClaimInterface::AUD_API,
                    ],
                ]
            ))
            ->assert($response)
        ;
    }

    public function assertApiTokenVerifySuccessResponse(ResponseInterface $response, User $expectedUser): void
    {
        (new TextPlainResponseAsserter(200))
            ->addBodyAsserter(new TextPlainBodyAsserter($expectedUser->getId()))
            ->assert($response)
        ;
    }

    public function assertFrontendTokenCreateSuccessResponse(
        ResponseInterface $response,
        User $expectedUser,
        string $expectedRefreshToken
    ): void {
        (new JsonResponseAsserter(200))
            ->addBodyAsserter($this->jwtTokenBodyAsserterFactory->create(
                'token',
                [
                    TokenInterface::CLAIM_EMAIL => $expectedUser->getUserIdentifier(),
                    TokenInterface::CLAIM_USER_ID => $expectedUser->getId(),
                    TokenInterface::CLAIM_AUDIENCE => [
                        AudienceClaimInterface::AUD_FRONTEND,
                    ],
                ]
            ))
            ->addBodyAsserter(
                new ArrayBodyAsserter([
                    'refresh_token' => $expectedRefreshToken,
                ])
            )
            ->assert($response)
        ;
    }

    public function assertFrontendTokenVerifySuccessResponse(ResponseInterface $response, User $expectedUser): void
    {
        (new JsonResponseAsserter(200))
            ->addBodyAsserter(new ArrayBodyAsserter([
                'id' => $expectedUser->getId(),
                'user-identifier' => $expectedUser->getUserIdentifier(),
            ]))
            ->assert($response)
        ;
    }

    public function assertAdminUnauthorizedResponse(ResponseInterface $response): void
    {
        Assert::assertSame(401, $response->getStatusCode());

        $response->getBody()->rewind();
        Assert::assertSame('', $response->getBody()->getContents());
    }

    public function assertHealthCheckResponse(ResponseInterface $response): void
    {
        $expectedData = [
            'database_connection' => true,
            'database_entities' => true,
            'jwt_configuration' => true,
        ];

        $healthCheckArrayBodyAsserter = new ArrayBodyAsserter($expectedData);
        $healthCheckArrayBodyAsserter = $healthCheckArrayBodyAsserter->errorOnAdditionalActualKeys();

        (new JsonResponseAsserter(200))
            ->addBodyAsserter($healthCheckArrayBodyAsserter)
            ->assert($response)
        ;
    }

    public function assertStatusResponse(ResponseInterface $response, bool $expectedReady): void
    {
        (new JsonResponseAsserter(200))
            ->addBodyAsserter(new ArrayBodyAsserter([
                'ready' => $expectedReady,
            ]))
            ->assert($response)
        ;
    }
}
