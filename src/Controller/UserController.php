<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserAlreadyExistsException;
use App\Request\CreateUserRequest;
use App\Services\UserFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class UserController
{
    public function __construct(
        private UserFactory $userFactory,
    ) {
    }

    public function create(CreateUserRequest $request): Response
    {
        $userCreated = false;

        try {
            $user = $this->userFactory->create($request->identifier, $request->password);
            $userCreated = true;
        } catch (UserAlreadyExistsException $userAlreadyExistsException) {
            $user = $userAlreadyExistsException->getUser();
        }

        return new JsonResponse($user, true === $userCreated ? 200 : 409);
    }
}
