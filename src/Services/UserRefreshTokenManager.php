<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class UserRefreshTokenManager
{
    public function __construct(
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function deleteByUserId(string $id): bool
    {
        $user = $this->userRepository->find($id);
        if (null === $user) {
            return false;
        }

        $hasDeleted = false;

        while (null !== $refreshToken = $this->refreshTokenManager->getLastFromUsername($user->getUserIdentifier())) {
            $this->refreshTokenManager->delete($refreshToken);
            $hasDeleted = true;
        }

        return $hasDeleted;
    }
}
