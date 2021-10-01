<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

class ResponseAsserter
{
    /**
     * @var HeaderAsserterInterface[]
     */
    private array $headerAsserters = [];

    /**
     * @var BodyAsserterInterface[]
     */
    private array $bodyAsserters = [];

    /**
     * @param int $expectedStatusCode
     */
    public function __construct(
        private int $expectedStatusCode
    ) {
    }

    public function addHeaderAsserter(HeaderAsserterInterface $headerAsserter): self
    {
        $this->headerAsserters[] = $headerAsserter;

        return $this;
    }

    public function addBodyAsserter(BodyAsserterInterface $bodyAsserter): self
    {
        $this->bodyAsserters[] = $bodyAsserter;

        return $this;
    }

    public function assert(ResponseInterface $response): void
    {
        Assert::assertSame($this->expectedStatusCode, $response->getStatusCode());

        foreach ($this->headerAsserters as $headerAsserter) {
            if ($headerAsserter instanceof HeaderAsserterInterface) {
                $headerAsserter->assert($response->getHeaders());
            }
        }

        $response->getBody()->rewind();
        $body = $response->getBody()->getContents();

        foreach ($this->bodyAsserters as $bodyAsserter) {
            if ($bodyAsserter instanceof BodyAsserterInterface) {
                $bodyAsserter->assert($body);
            }
        }
    }

    public function assertFromSymfonyResponse(Response $response): void
    {
        $psr17Factory = new HttpFactory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $this->assert($psrHttpFactory->createResponse($response));
    }
}
