<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use Symfony\Component\Uid\Ulid;

class ApiKeyFactory
{
    public function __construct(
        private readonly ApiKeyRepository $repository,
    ) {
    }

    /**
     * @param non-empty-string $label
     */
    public function create(string $label, User $user): ApiKey
    {
        $key = $this->repository->findOneBy([
            'owner' => $user,
            'label' => $label,
        ]);

        if (null === $key) {
            $key = $this->repository->add(new ApiKey((string) new Ulid(), $label, $user));
        }

        return $key;
    }
}
