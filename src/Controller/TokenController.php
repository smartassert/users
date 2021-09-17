<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TokenController extends AbstractController
{
    public const ROUTE_CREATE = '/token/create';

    #[Route(self::ROUTE_CREATE, name: 'token_create')]
    public function create(): void
    {
    }
}
