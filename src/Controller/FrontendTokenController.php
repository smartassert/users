<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class FrontendTokenController extends AbstractController
{
    public function verify(UserInterface $user): JsonResponse
    {
        return new JsonResponse($user);
    }
}
