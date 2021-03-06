<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class UserRefreshTokenManager
{
    public function __construct(
        private RefreshTokenManagerInterface $refreshTokenManager,
        private UserRepository $userRepository,
    ) {
    }

    public function deleteByUserId(string $id): bool
    {
        $user = $this->userRepository->find($id);
        if (null === $user) {
            return false;
        }

        $refreshToken = $this->refreshTokenManager->getLastFromUsername($user->getUserIdentifier());
        if (null === $refreshToken) {
            return false;
        }

        $this->refreshTokenManager->delete($refreshToken);

        return true;
    }
}
