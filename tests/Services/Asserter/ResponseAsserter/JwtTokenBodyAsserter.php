<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use App\Tests\Services\Asserter\AssociativeArrayAsserter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Assert;

class JwtTokenBodyAsserter extends ArrayBodyAsserter implements BodyAsserterInterface
{
    private JWTTokenManagerInterface $JWTTokenManager;
    private string $expectedTokenKey;
    private AssociativeArrayAsserter $payloadAsserter;

    /**
     * @param array<string, null|bool|int|string|string[]> $expectedPayload
     */
    public function __construct(
        JWTTokenManagerInterface $JWTTokenManager,
        string $expectedTokenKey,
        array $expectedPayload
    ) {
        $this->JWTTokenManager = $JWTTokenManager;
        $this->expectedTokenKey = $expectedTokenKey;
        $this->payloadAsserter = (new AssociativeArrayAsserter($expectedPayload));

        parent::__construct([
            $expectedTokenKey => null,
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function assert(string $body): array
    {
        $data = parent::assert($body);
        $token = $data[$this->expectedTokenKey];
        Assert::assertIsString($token);

        $payload = $this->JWTTokenManager->parse($token);
        Assert::assertIsArray($payload);

        $this->payloadAsserter->assert($payload);

        return $payload;
    }
}
