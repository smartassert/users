<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient implements ClientInterface
{
    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function makeRequest(
        string $method,
        string $uri,
        array $headers = [],
        ?string $body = null
    ): ResponseInterface {
        return $this->httpClient->sendRequest(
            $this->createRequest($method, $uri, $headers, $body)
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(
        string $method,
        string $uri,
        array $headers = [],
        ?string $body = null
    ): RequestInterface {
        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        if (is_string($body)) {
            $request = $request->withBody(Utils::streamFor($body));
        }

        return $request;
    }
}
