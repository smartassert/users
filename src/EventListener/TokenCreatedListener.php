<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Security\IdentifiableUserInterface;
use App\Security\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class TokenCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();
        if ($user instanceof IdentifiableUserInterface) {
            $payload[TokenInterface::CLAIM_USER_ID] = $user->getId();
        }

        $event->setData($payload);
    }
}
