<?php

declare(strict_types=1);

namespace App\Security;

readonly class JwtToken
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(
        private array $data,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAudience(): array
    {
        $audClaim = $this->data['aud'] ?? [];
        if (!is_array($audClaim)) {
            $audClaim = [];
        }

        $audience = [];
        foreach ($audClaim as $foo) {
            if (is_string($foo)) {
                $audience[] = $foo;
            }
        }

        return $audience;
    }

    public function hasAudience(string $audience): bool
    {
        return in_array($audience, $this->getAudience());
    }
}
