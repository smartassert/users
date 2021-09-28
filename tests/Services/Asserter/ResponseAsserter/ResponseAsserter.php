<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class ResponseAsserter
{
    private int $expectedStatusCode = 200;
    private ?HeaderAsserterInterface $headerAsserter = null;
    private ?BodyAsserterInterface $bodyAsserter = null;

    /**
     * @var class-string
     */
    private string $expectedClass = Response::class;

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

    public function withExpectedHeaders(HeaderAsserterInterface $headerAsserter): static
    {
        $new = clone $this;
        $new->headerAsserter = $headerAsserter;

        return $new;
    }

    public function withExpectedBody(BodyAsserterInterface $bodyAsserter): static
    {
        $new = clone $this;
        $new->bodyAsserter = $bodyAsserter;

        return $new;
    }

    public function assert(Response $response): void
    {
        Assert::assertSame($this->expectedStatusCode, $response->getStatusCode());
        Assert::assertInstanceOf($this->expectedClass, $response);

        if ($this->headerAsserter instanceof HeaderAsserterInterface) {
            $this->headerAsserter->assert($response->headers);
        }

        if ($this->bodyAsserter instanceof BodyAsserterInterface) {
            $this->bodyAsserter->assert((string) $response->getContent());
        }
    }
}
