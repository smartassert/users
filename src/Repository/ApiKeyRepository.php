<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method null|ApiKey find($id, $lockMode = null, $lockVersion = null)
 * @method null|ApiKey findOneBy(array $criteria, array $orderBy = null)
 * @method ApiKey[]    findAll()
 * @method ApiKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ApiKey>
 */
class ApiKeyRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKey::class);
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
        if ($apiKey instanceof ApiKey) {
            return $apiKey->owner;
        }

        return null;
    }
}
