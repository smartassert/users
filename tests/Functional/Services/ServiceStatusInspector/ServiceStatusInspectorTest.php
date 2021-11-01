<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services\ServiceStatusInspector;

use App\Tests\Functional\AbstractBaseFunctionalTest;
use SmartAssert\ServiceStatusInspector\ServiceStatusInspector;
use SmartAssert\ServiceStatusInspector\ServiceStatusInspectorInterface;
use webignition\ObjectReflector\ObjectReflector;

class ServiceStatusInspectorTest extends AbstractBaseFunctionalTest
{
    private ServiceStatusInspector $serviceStatusInspector;

    protected function setUp(): void
    {
        parent::setUp();

        $serviceStatusInspector = self::getContainer()->get(ServiceStatusInspectorInterface::class);
        \assert($serviceStatusInspector instanceof ServiceStatusInspector);
        $this->serviceStatusInspector = $serviceStatusInspector;
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param callable[]          $modifiedComponentInspectors
     * @param array<string, bool> $expectedServiceStatus
     */
    public function testGet(
        array $modifiedComponentInspectors,
        array $expectedServiceStatus
    ): void {
        foreach ($modifiedComponentInspectors as $name => $componentInspector) {
            $this->setComponentInspector($name, $componentInspector);
        }

        self::assertEquals($expectedServiceStatus, $this->serviceStatusInspector->get());
    }

    /**
     * @return array<mixed>
     */
    public function getDataProvider(): array
    {
        return [
            'all services available' => [
                'modifiedComponentInspectors' => [],
                'expectedServiceStatus' => [
                    'database_connection' => true,
                    'database_entities' => true,
                ],
            ],
            'database connection unavailable' => [
                'modifiedComponentInspectors' => [
                    'database_connection' => $this->createComponentInspectorThrowingException(),
                ],
                'expectedServiceStatus' => [
                    'database_connection' => false,
                    'database_entities' => true,
                ],
            ],
            'database entities unavailable' => [
                'modifiedComponentInspectors' => [
                    'database_entities' => $this->createComponentInspectorThrowingException(),
                ],
                'expectedServiceStatus' => [
                    'database_connection' => true,
                    'database_entities' => false,
                ],
            ],
            'all services unavailable' => [
                'modifiedComponentInspectors' => [
                    'database_connection' => $this->createComponentInspectorThrowingException(),
                    'database_entities' => $this->createComponentInspectorThrowingException(),
                ],
                'expectedServiceStatus' => [
                    'database_connection' => false,
                    'database_entities' => false,
                ],
            ],
        ];
    }

    private function setComponentInspector(string $name, callable $componentInspector): void
    {
        $componentInspectors = ObjectReflector::getProperty(
            $this->serviceStatusInspector,
            'componentInspectors',
            ServiceStatusInspector::class
        );

        if (array_key_exists($name, $componentInspectors)) {
            $componentInspectors[$name] = $componentInspector;
        }

        ObjectReflector::setProperty(
            $this->serviceStatusInspector,
            ServiceStatusInspector::class,
            'componentInspectors',
            $componentInspectors
        );
    }

    private function createComponentInspectorThrowingException(): callable
    {
        return function () {
            throw new \Exception();
        };
    }
}
