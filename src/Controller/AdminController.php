<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminController extends AbstractController
{
    public const ROUTE_PREFIX = '/admin';
    public const ROUTE_ADMIN_USER_CREATE = self::ROUTE_PREFIX . '/user/create';

    #[Route(self::ROUTE_ADMIN_USER_CREATE, name: 'admin_user_create')]
    public function createUser(UserInterface $user): Response
    {
        return new Response($user->getUserIdentifier());
    }
}
