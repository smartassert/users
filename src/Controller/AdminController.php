<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserAlreadyExistsException;
use App\Request\CreateUserRequest;
use App\Response\BadRequestResponse;
use App\Response\BadRequestValueMissingResponse;
use App\Services\UserFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public const ROUTE_PREFIX = '/admin';
    public const ROUTE_ADMIN_USER_CREATE = self::ROUTE_PREFIX . '/user/create';

    #[Route(self::ROUTE_ADMIN_USER_CREATE, name: 'admin_user_create', methods: ['POST'])]
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
}
