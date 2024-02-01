<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RefreshToken;
use App\Request\RevokeRefreshTokenRequest;
use App\Services\UserRefreshTokenManager;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class RefreshTokenController
{
    public function __construct(
        private UserRefreshTokenManager $tokenManager,
    ) {
    }

    public function revokeAllForUser(RevokeRefreshTokenRequest $request): Response
    {
        $this->tokenManager->deleteByUserId($request->id);

        return new Response(null, 200, ['content-type' => null]);
    }

    public function revoke(Request $request, RefreshTokenManagerInterface $refreshTokenManager): Response
    {
        $refreshToken = $request->request->getString('refresh_token');
        if ('' === $refreshToken) {
            return new Response();
        }

        $refreshTokenEntity = $refreshTokenManager->get($refreshToken);
        if ($refreshTokenEntity instanceof RefreshToken) {
            $refreshTokenManager->delete($refreshTokenEntity);
        }

        return new Response(null, 200, ['content-type' => null]);
    }
}
