<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class ResponseAsserter
{
    private int $expectedStatusCode = 200;

    /**
     * @var class-string
     */
    private string $expectedClass = Response::class;

    /**
     * @var HeaderAsserterInterface[]
     */
    private array $headerAsserters = [];

    /**
     * @var BodyAsserterInterface[]
     */
    private array $bodyAsserters = [];

    public static function create(): self
    {
        return new self();
    }

    public function withExpectedStatusCode(int $expectedStatusCode): static
    {
        $new = clone $this;
        $new->expectedStatusCode = $expectedStatusCode;

        return $new;
    }

    /**
     * @param class-string $expectedClass
     */
    public function withExpectedClass(string $expectedClass): static
    {
        $new = clone $this;
        $new->expectedClass = $expectedClass;

        return $new;
    }

    public function addHeaderAsserter(HeaderAsserterInterface $headerAsserter): static
    {
        $new = clone $this;
        $new->headerAsserters[] = $headerAsserter;

        return $new;
    }

    public function addBodyAsserter(BodyAsserterInterface $bodyAsserter): static
    {
        $new = clone $this;
        $new->bodyAsserters[] = $bodyAsserter;

        return $new;
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
