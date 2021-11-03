<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\ApplicationResponseAsserter;

class StatusControllerTest extends AbstractBaseWebTestCase
{
    private ApplicationResponseAsserter $applicationResponseAsserter;

    protected function setUp(): void
    {
        parent::setUp();

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);
        $this->applicationResponseAsserter = $applicationResponseAsserter;
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(int $envReady, bool $expectedReady): void
    {
        $_ENV['IS_READY'] = $envReady;

        $response = $this->application->makeStatusRequest();
        $this->applicationResponseAsserter->assertStatusResponse($response, $expectedReady);
    }

    /**
     * @return array<mixed>
     */
    public function getDataProvider(): array
    {
        return [
            'ENV READY=0' => [
                'envReady' => 0,
                'expectedReady' => false,
            ],
            'ENV READY=1' => [
                'envReady' => 1,
                'expectedReady' => true,
            ],
        ];
    }
}
