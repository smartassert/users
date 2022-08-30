<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\ApiKeyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyController
{
    public function list(ApiKeyRepository $apiKeyRepository, ?User $user): Response
    {
        $serializedApiKeys = [];
        $apiKeys = $apiKeyRepository->findBy(['owner' => $user]);

        foreach ($apiKeys as $apiKey) {
            $serializedApiKeys[] = [
                'label' => $apiKey->label,
                'key' => $apiKey->id,
            ];
        }

        return new JsonResponse($serializedApiKeys);
    }
}
