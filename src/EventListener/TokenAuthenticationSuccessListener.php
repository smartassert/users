<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Security\JwtToken;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

readonly class TokenAuthenticationSuccessListener
{
    public function __construct(
        private JWTTokenManagerInterface $jwtTokenManager,
        private ApiKeyRepository $apiKeyRepository,
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

        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $apiKey = $this->apiKeyRepository->getDefault($user);
        if ($apiKey instanceof ApiKey) {
            $eventData = $event->getData();
            $eventData['api_key'] = $apiKey->id;

            $event->setData($eventData);
        }
    }
}
