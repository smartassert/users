<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class TokenCreatedListener
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();

        $user = $this->userRepository->findByEmail($payload[TokenInterface::CLAIM_USER_IDENTIFIER] ?? '');
        if ($user instanceof User) {
            $payload[TokenInterface::CLAIM_USER_ID] = $user->getId();
        }

        $event->setData($payload);
    }
}
