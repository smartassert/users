<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class FrontendTokenController extends AbstractController
{
    public const ROUTE_CREATE = '/frontend/token/create';
    public const ROUTE_VERIFY = '/frontend/token/verify';

    #[Route(self::ROUTE_CREATE, name: 'frontend_token_create')]
    public function create(): void
    {
    }

    #[Route(self::ROUTE_VERIFY, name: 'frontend_token_verify')]
    public function index(UserInterface $user): JsonResponse
    {
        return new JsonResponse($user);
    }
}
