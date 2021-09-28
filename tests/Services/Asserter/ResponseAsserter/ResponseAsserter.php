<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use PHPUnit\Framework\Assert;
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
     * @param int          $expectedStatusCode
     * @param class-string $expectedClass
     */
    public function __construct(
        private int $expectedStatusCode,
        private string $expectedClass
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

    public function assert(Response $response): void
    {
        Assert::assertSame($this->expectedStatusCode, $response->getStatusCode());
        Assert::assertInstanceOf($this->expectedClass, $response);

        foreach ($this->headerAsserters as $headerAsserter) {
            if ($headerAsserter instanceof HeaderAsserterInterface) {
                $headerAsserter->assert($response->headers);
            }
        }

        foreach ($this->bodyAsserters as $bodyAsserter) {
            if ($bodyAsserter instanceof BodyAsserterInterface) {
                $bodyAsserter->assert((string) $response->getContent());
            }
        }
    }
}
