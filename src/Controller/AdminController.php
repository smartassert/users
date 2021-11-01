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
    public function createUser(
        CreateUserRequest $createUserRequest,
        UserFactory $userFactory,
    ): Response {
        $email = $createUserRequest->getEmail();
        if ('' === $email) {
            return new BadRequestValueMissingResponse('email');
        }

        $password = $createUserRequest->getPassword();
        if ('' === $password) {
            return new BadRequestValueMissingResponse('password');
        }

        try {
            $user = $userFactory->create($email, $password);
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
        UserRefreshTokenManager $userRefreshTokenManager,
    ): Response {
        $id = $request->getId();
        if ('' === $id) {
            return new BadRequestValueMissingResponse('id');
        }

        $userRefreshTokenManager->deleteByUserId($id);

        return new Response();
    }
}
