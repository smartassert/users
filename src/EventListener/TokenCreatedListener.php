<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
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

        $user = $this->userRepository->findByEmail($payload['username'] ?? '');
        if ($user instanceof User) {
            $payload['id'] = $user->getId();
        }

        $event->setData($payload);
    }
}
