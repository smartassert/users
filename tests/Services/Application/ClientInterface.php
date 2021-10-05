<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function makeRequest(
        string $method,
        string $uri,
        array $headers = [],
        ?string $body = null
    ): ResponseInterface;
}
