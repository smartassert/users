<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractIntegrationTest extends WebTestCase
{
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new Client([
            'base_uri' => 'http://localhost:9090/'
        ]);

        $this->requestFactory = new HttpFactory();
    }
}
