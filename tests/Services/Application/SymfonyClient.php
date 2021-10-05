<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SymfonyClient implements ClientInterface
{
    private KernelBrowser $kernelBrowser;

    public function __construct(
        private HttpMessageFactoryInterface $httpMessageFactory,
    ) {
    }

    public function setKernelBrowser(KernelBrowser $kernelBrowser): void
    {
        $this->kernelBrowser = $kernelBrowser;
    }

    public function makeRequest(
        string $method,
        string $uri,
        array $headers = [],
        ?string $body = null
    ): ResponseInterface {
        $headers = $this->prepareHeaders($headers);
        $parameters = $this->prepareParameters($method, $headers, $body);
        $body = $this->prepareBody($method, $headers, $body);

        $this->kernelBrowser->request($method, $uri, $parameters, [], $headers, $body);

        $symfonyResponse = $this->kernelBrowser->getResponse();

        $response = $this->httpMessageFactory->createResponse($symfonyResponse);
        $response->getBody()->rewind();

        return $response;
    }

    /**
     * @param array<mixed> $headers
     *
     * @return array<mixed>
     */
    private function prepareHeaders(array $headers): array
    {
        foreach ($headers as $key => $value) {
            $headers[strtolower($key)] = $value;
        }

        $contentType = $headers['content-type'] ?? null;
        if (is_string($contentType)) {
            $headers['CONTENT_TYPE'] = $contentType;
        }

        $authorization = $headers['authorization'] ?? null;
        if (is_string($authorization)) {
            $headers['HTTP_AUTHORIZATION'] = $authorization;
        }

        return $headers;
    }

    /**
     * @param array<mixed> $headers
     *
     * @return array<mixed>
     */
    private function prepareParameters(string $method, array $headers, ?string $body): array
    {
        if (null === $body) {
            return [];
        }

        if ('POST' === $method) {
            $contentType = $headers['content-type'] ?? null;

            if ('application/x-www-form-urlencoded' === $contentType) {
                parse_str($body, $parameters);

                return $parameters;
            }
        }

        return [];
    }

    /**
     * @param array<mixed> $headers
     */
    private function prepareBody(string $method, array $headers, ?string $body): ?string
    {
        if (null === $body) {
            return null;
        }

        if ('POST' === $method) {
            $contentType = $headers['content-type'] ?? null;

            if ('application/x-www-form-urlencoded' === $contentType) {
                return null;
            }
        }

        return $body;
    }
}
