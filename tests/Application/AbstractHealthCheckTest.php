<?php

declare(strict_types=1);

namespace App\Tests\Application;

use PHPUnit\Framework\Assert;

abstract class AbstractHealthCheckTest extends AbstractApplicationTest
{
    public function testGetHealthCheck(): void
    {
        $response = $this->applicationClient->makeHealthCheckRequest();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        $expectedResponseData = [
            'database_connection' => true,
            'database_entities' => true,
            'jwt_configuration' => true,
        ];

        Assert::assertEquals($expectedResponseData, $responseData);
    }
}
