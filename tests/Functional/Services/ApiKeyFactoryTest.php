<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Services\ApiKeyFactory;
use App\Tests\Functional\AbstractBaseFunctionalTest;
use App\Tests\Services\TestUserFactory;
use webignition\ObjectReflector\ObjectReflector;

class ApiKeyFactoryTest extends AbstractBaseFunctionalTest
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

        $apiKey = $this->apiKeyFactory->create($label, $this->user);
        self::assertCount(1, $this->apiKeyRepository->findAll());
        self::assertSame($this->user, $apiKey->owner);

        $retrievedApiKey = $this->apiKeyRepository->findOneBy([
            'id' => ObjectReflector::getProperty($apiKey, 'id'),
        ]);

        self::assertEquals($apiKey, $retrievedApiKey);
    }

    /**
     * @dataProvider createIsIdempotentDataProvider
     */
    public function testCreateIsIdempotent(?string $label): void
    {
        self::assertCount(0, $this->apiKeyRepository->findAll());

        $this->apiKeyFactory->create($label, $this->user);
        $this->apiKeyFactory->create($label, $this->user);
        $this->apiKeyFactory->create($label, $this->user);

        self::assertCount(1, $this->apiKeyRepository->findAll());
    }

    /**
     * @return array<mixed>
     */
    public function createIsIdempotentDataProvider(): array
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
