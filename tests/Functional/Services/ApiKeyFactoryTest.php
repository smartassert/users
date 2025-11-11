<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Services\ApiKeyFactory;
use App\Tests\Functional\AbstractBaseFunctionalTestCase;
use App\Tests\Services\TestUserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

class ApiKeyFactoryTest extends AbstractBaseFunctionalTestCase
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

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->user = $testUserFactory->create();
    }

    public function testCreate(): void
    {
        self::assertCount(0, $this->apiKeyRepository->findAll());

        $label = 'api key label';

        $apiKey = $this->apiKeyFactory->create($this->user, $label);
        self::assertCount(1, $this->apiKeyRepository->findAll());
        self::assertSame($this->user->getId(), $apiKey->ownerId);

        $retrievedApiKey = $this->apiKeyRepository->findOneBy(['id' => $apiKey->id]);

        self::assertEquals($apiKey, $retrievedApiKey);
    }

    /**
     * @param ?non-empty-string $label
     */
    #[DataProvider('createIsIdempotentDataProvider')]
    public function testCreateIsIdempotent(?string $label): void
    {
        self::assertCount(0, $this->apiKeyRepository->findAll());

        $this->apiKeyFactory->create($this->user, $label);
        $this->apiKeyFactory->create($this->user, $label);
        $this->apiKeyFactory->create($this->user, $label);

        self::assertCount(1, $this->apiKeyRepository->findAll());
    }

    /**
     * @return array<mixed>
     */
    public static function createIsIdempotentDataProvider(): array
    {
        return [
            'null' => [
                'label' => null,
            ],
            'empty' => [
                'label' => '',
            ],
            'non-empty' => [
                'label' => 'non-empty value',
            ],
        ];
    }
}
