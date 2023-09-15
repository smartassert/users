<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserAlreadyExistsException;
use App\Request\CreateUserRequest;
use App\Response\BadRequestValueMissingResponse;
use App\Services\ApiKeyFactory;
use App\Services\UserFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController
{
    public function create(CreateUserRequest $request, UserFactory $userFactory, ApiKeyFactory $apiKeyFactory): Response
    {
        if (null === $request->email) {
            return new BadRequestValueMissingResponse('email');
        }

        if (null === $request->password) {
            return new BadRequestValueMissingResponse('password');
        }

        $userCreated = false;

        try {
            $user = $userFactory->create($request->email, $request->password);
            $apiKeyFactory->create($user);
            $userCreated = true;
        } catch (UserAlreadyExistsException $userAlreadyExistsException) {
            $user = $userAlreadyExistsException->getUser();
        }

        return new JsonResponse(
            [
                'user' => $user,
            ],
            true === $userCreated ? 200 : 409
        );
    }
}
