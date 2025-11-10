<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiKey;
use App\Security\IdentifiableUserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<ApiKey>
 */
class ApiKeyRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    private readonly UserRepository $userRepository;

    public function __construct(ManagerRegistry $registry, UserRepository $userRepository)
    {
        parent::__construct($registry, ApiKey::class);
        $this->userRepository = $userRepository;
    }

    public function add(ApiKey $entity): ApiKey
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity;
    }

    /**
     * Required by the api_user_provider entity-based user provider.
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $apiKey = $this->find($identifier);

        if (!$apiKey instanceof ApiKey) {
            return null;
        }

        return $this->userRepository->findOneBy(['id' => $apiKey->ownerId]);
    }

    /**
     * @return ApiKey[]
     */
    public function findAllNonDefaultForUser(IdentifiableUserInterface $owner): array
    {
        $queryBuilder = $this->createQueryBuilder('ApiKey');
        $queryBuilder
            ->where('ApiKey.ownerId = :Owner AND ApiKey.label IS NOT NULL')
            ->setParameter('Owner', $owner->getId())
        ;

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        $apiKeys = [];
        if (!is_iterable($result)) {
            return $apiKeys;
        }

        foreach ($result as $apiKey) {
            if ($apiKey instanceof ApiKey) {
                $apiKeys[] = $apiKey;
            }
        }

        return $apiKeys;
    }
}
