<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\RevokeRefreshTokenRequest;
use App\Response\BadRequestValueMissingResponse;
use App\Services\UserRefreshTokenManager;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenController
{
    public function revoke(RevokeRefreshTokenRequest $request, UserRefreshTokenManager $tokenManager): Response
    {
        if (null === $request->id) {
            return new BadRequestValueMissingResponse('id');
        }

        $tokenManager->deleteByUserId($request->id);

        return new Response();
    }
}
