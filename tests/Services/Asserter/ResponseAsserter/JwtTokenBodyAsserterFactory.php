<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JwtTokenBodyAsserterFactory
{
    public function __construct(
        private JWTTokenManagerInterface $JWTTokenManager
    ) {
    }

    /**
     * @param array<string, null|bool|int|string> $expectedPayload $expectedPayload
     */
    public function create(string $expectedTokenKey, array $expectedPayload): JwtTokenBodyAsserter
    {
        return new JwtTokenBodyAsserter(
            $this->JWTTokenManager,
            $expectedTokenKey,
            $expectedPayload,
        );
    }
}
