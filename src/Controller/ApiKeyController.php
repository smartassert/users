<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class ApiKeyController
{
    public function __construct(
        private ApiKeyRepository $apiKeyRepository,
    ) {
    }

    public function list(User $user): Response
    {
        $serializedApiKeys = [];
        $apiKeys = $this->apiKeyRepository->findBy(['owner' => $user]);

        foreach ($apiKeys as $apiKey) {
            $serializedApiKeys[] = $this->serializeApiKey($apiKey);
        }

        return new JsonResponse($serializedApiKeys);
    }

    public function getDefault(User $user): Response
    {
        $apiKey = $this->apiKeyRepository->findOneBy([
            'owner' => $user,
            'label' => null,
        ]);

        return $apiKey instanceof ApiKey
            ? new JsonResponse($this->serializeApiKey($apiKey))
            : new Response(null, 404);
    }

    /**
     * @return array{label: ?non-empty-string, key: non-empty-string}
     */
    private function serializeApiKey(ApiKey $apiKey): array
    {
        return [
            'label' => $apiKey->label,
            'key' => $apiKey->id,
        ];
    }
}
