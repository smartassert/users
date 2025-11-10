<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use App\Security\IdentifiableUserInterface;
use Symfony\Component\Uid\Ulid;

class ApiKeyFactory
{
    public function __construct(
        private readonly ApiKeyRepository $repository,
    ) {
    }

    /**
     * @param ?non-empty-string $label
     */
    public function create(IdentifiableUserInterface $user, ?string $label = null): ApiKey
    {
        $key = $this->repository->findOneBy([
            'ownerId' => $user->getId(),
            'label' => $label,
        ]);

        if (null === $key) {
            $key = $this->repository->add(new ApiKey($this->generateId(), $label, $user->getId()));
        }

        return $key;
    }

    /**
     * @return non-empty-string
     */
    private function generateId(): string
    {
        do {
            $id = (string) new Ulid();
        } while ('' === $id);

        return $id;
    }
}
