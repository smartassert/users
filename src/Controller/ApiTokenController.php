<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\IdentifiableUserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiTokenController
{
    public const ROUTE_CREATE = '/api/token/create';
    public const ROUTE_VERIFY = '/api/token/verify';

    #[Route(self::ROUTE_VERIFY, name: 'api_token_verify')]
    public function index(UserInterface $user): Response
    {
        return new Response(
            $user instanceof IdentifiableUserInterface ? $user->getId() : '',
            200,
            [
                'content-type' => 'text/plain',
            ]
        );
    }
}
