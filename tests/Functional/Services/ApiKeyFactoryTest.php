<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Services\ApiKeyFactory;
use App\Services\UserFactory;
use App\Tests\Services\UserRemover;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiKeyFactoryTest extends WebTestCase
{
    private ApiKeyFactory $apiKeyFactory;
    private ApiKeyRepository $apiKeyRepository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $apiKeyFactory = self::getContainer()->get(ApiKeyFactory::class);
        \assert($apiKeyFactory instanceof ApiKeyFactory);
        $this->apiKeyFactory = $apiKeyFactory;

        $apiKeyRepository = self::getContainer()->get(ApiKeyRepository::class);
        \assert($apiKeyRepository instanceof ApiKeyRepository);
        $this->apiKeyRepository = $apiKeyRepository;

        $this->removeAllUsers();

        $userFactory = self::getContainer()->get(UserFactory::class);
        \assert($userFactory instanceof UserFactory);
        $this->user = $userFactory->create('user@example.com', 'password');
    }

    protected function tearDown(): void
    {
        $this->removeAllUsers();

        parent::tearDown();
    }

    public function testCreate(): void
    {
        self::assertCount(0, $this->apiKeyRepository->findAll());

        $label = 'api key label';

        $apiKey = $this->apiKeyFactory->create($label, $this->user);
        self::assertCount(1, $this->apiKeyRepository->findAll());
        self::assertSame($this->user, $apiKey->getOwner());

        $retrievedApiKey = $this->apiKeyRepository->findOneBy([
            'id' => $apiKey->getId(),
        ]);

        self::assertEquals($apiKey, $retrievedApiKey);
    }

    private function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }
    }
}
