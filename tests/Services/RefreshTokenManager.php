<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class RefreshTokenManager
{
    private RefreshTokenRepository $refreshTokenRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenManagerInterface $refreshTokenManager,
    ) {
        $refreshTokenRepository = $this->entityManager->getRepository(RefreshToken::class);
        if ($refreshTokenRepository instanceof RefreshTokenRepository) {
            $this->refreshTokenRepository = $refreshTokenRepository;
        }
    }

    public function create(User $user): RefreshTokenInterface
    {
        $token = $this->refreshTokenGenerator->createForUserWithTtl($user, 3600);
        $this->refreshTokenManager->save($token);

        return $token;
    }

    public function count(): int
    {
        return $this->refreshTokenRepository->count([]);
    }

    public function removeAll(): void
    {
        $refreshTokens = $this->refreshTokenRepository->findAll();

        foreach ($refreshTokens as $refreshToken) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->flush();
        }
    }
}
