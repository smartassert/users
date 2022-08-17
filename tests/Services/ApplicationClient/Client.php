<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\RouterInterface;

class Client
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @param array<mixed>          $routeParameters
     * @param array<string, string> $headers
     */
    public function makeRequest(
        string $method,
        string $routeName,
        array $routeParameters,
        array $headers = [],
        ?string $body = null
    ): ResponseInterface {
        return $this->client->makeRequest(
            $method,
            $this->router->generate($routeName, $routeParameters),
            $headers,
            $body
        );
    }
}
