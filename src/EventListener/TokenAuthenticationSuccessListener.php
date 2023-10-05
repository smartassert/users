<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Security\JwtToken;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

readonly class TokenAuthenticationSuccessListener
{
    public function __construct(
        private JWTTokenManagerInterface $jwtTokenManager,
    ) {
    }

    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $rawToken = $event->getData()['token'] ?? null;
        if (!is_string($rawToken)) {
            return;
        }

        $token = new JwtToken($this->jwtTokenManager->parse($rawToken));
        if ($token->hasAudience('api')) {
            $event->stopPropagation();
        }
    }
}
