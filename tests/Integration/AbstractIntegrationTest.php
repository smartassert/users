<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\ApiKeyFactory;
use App\Tests\Services\ApplicationInterface;
use App\Tests\Services\ApplicationResponseAsserter;
use App\Tests\Services\IntegrationApplication;
use App\Tests\Services\UserRemover;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractIntegrationTest extends WebTestCase
{
    protected const TEST_USER_EMAIL = 'user@example.com';
    protected const TEST_USER_PASSWORD = 'user-password';

    protected ApplicationInterface $application;
    protected ApplicationResponseAsserter $applicationResponseAsserter;
    protected ApiKeyFactory $apiKeyFactory;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        static::createClient();

        $application = self::getContainer()->get(IntegrationApplication::class);
        \assert($application instanceof IntegrationApplication);
        $this->application = $application;

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);
        $this->applicationResponseAsserter = $applicationResponseAsserter;

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $this->apiKeyFactory = $apiKeyFactory;
    }

    protected function getTestUser(): User
    {
        $this->createTestUser();

        $user = $this->userRepository->findByEmail(self::TEST_USER_EMAIL);
        \assert($user instanceof User);

        return $user;
    }

    protected function createTestUser(): ResponseInterface
    {
        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        return $this->application->makeAdminCreateUserRequest(
            self::TEST_USER_EMAIL,
            self::TEST_USER_PASSWORD,
            $adminToken
        );
    }

    protected function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        \assert($userRemover instanceof UserRemover);
        $userRemover->removeAll();
    }

    protected function removeAllRefreshTokens(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $refreshTokenRepository = $entityManager->getRepository(RefreshToken::class);
        \assert($refreshTokenRepository instanceof RefreshTokenRepository);

        $refreshTokens = $refreshTokenRepository->findAll();

        foreach ($refreshTokens as $refreshToken) {
            $entityManager->remove($refreshToken);
            $entityManager->flush();
        }
    }

    protected function getAdminToken(): string
    {
        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        return $adminToken;
    }
}
