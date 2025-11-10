<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use App\Security\IdentifiableUserInterface;
use App\Services\ApiKeyFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class ApiKeyController
{
    public function __construct(
        private ApiKeyRepository $apiKeyRepository,
        private ApiKeyFactory $apiKeyFactory,
    ) {
    }

    public function list(IdentifiableUserInterface $user): Response
    {
        $serializedApiKeys = [];
        foreach ($this->apiKeyRepository->findAllNonDefaultForUser($user) as $apiKey) {
            $serializedApiKeys[] = $this->serializeApiKey($apiKey);
        }

        return new JsonResponse($serializedApiKeys);
    }

    public function getDefault(IdentifiableUserInterface $user): Response
    {
        $apiKey = $this->apiKeyRepository->findOneBy(['ownerId' => $user->getId(), 'label' => null]);
        if (null === $apiKey) {
            $apiKey = $this->apiKeyFactory->create($user);
        }

        return new JsonResponse($this->serializeApiKey($apiKey));
    }

    /**
     * @return array{label: ?string, key: string}
     */
    private function serializeApiKey(ApiKey $apiKey): array
    {
        return [
            'label' => $apiKey->label,
            'key' => $apiKey->id,
        ];
    }
}
