<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiKey;

use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\ApplicationResponseAsserter;
use App\Tests\Services\TestUserFactory;

class ListTest extends AbstractBaseWebTestCase
{
    private TestUserFactory $testUserFactory;
    private ApplicationResponseAsserter $applicationResponseAsserter;

    protected function setUp(): void
    {
        parent::setUp();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->testUserFactory = $testUserFactory;

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);
        $this->applicationResponseAsserter = $applicationResponseAsserter;
    }

    public function testListUnauthorized(): void
    {
        $response = $this->application->makeFrontendListApiKeysRequest(...$this->testUserFactory->getCredentials());

        $this->applicationResponseAsserter->assertFrontendUnauthorizedResponse($response);
    }

    public function testListSuccess(): void
    {
        $this->testUserFactory->create();

        $response = $this->application->makeFrontendListApiKeysRequest(...$this->testUserFactory->getCredentials());

        $this->applicationResponseAsserter->assertFrontendListApiKeysResponse($response, []);
    }
}
