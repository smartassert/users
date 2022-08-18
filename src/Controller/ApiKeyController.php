<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiKeyController
{
    public function list(?UserInterface $user): Response
    {
        return new JsonResponse([]);
    }
}
