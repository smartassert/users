<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserAlreadyExistsException;
use App\Request\CreateUserRequest;
use App\Request\RevokeRefreshTokenRequest;
use App\Response\BadRequestResponse;
use App\Response\BadRequestValueMissingResponse;
use App\Services\UserFactory;
use App\Services\UserRefreshTokenManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminController
{
    public function createUser(CreateUserRequest $request, UserFactory $userFactory): Response
    {
        if (null === $request->email) {
            return new BadRequestValueMissingResponse('email');
        }

        if (null === $request->password) {
            return new BadRequestValueMissingResponse('password');
        }

        try {
            $user = $userFactory->create($request->email, $request->password);
        } catch (UserAlreadyExistsException $userAlreadyExistsException) {
            return new BadRequestResponse(
                'User already exists',
                ['user' => $userAlreadyExistsException->getUser()],
            );
        }

        return new JsonResponse([
            'user' => $user,
        ]);
    }

    public function revokeRefreshToken(
        RevokeRefreshTokenRequest $request,
        UserRefreshTokenManager $tokenManager,
    ): Response {
        if (null === $request->id) {
            return new BadRequestValueMissingResponse('id');
        }

        $tokenManager->deleteByUserId($request->id);

        return new Response();
    }
}
