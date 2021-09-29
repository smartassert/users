<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\IdentifiableUserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiTokenController
{
    public function verify(UserInterface $user): Response
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
