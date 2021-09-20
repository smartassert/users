<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TokenController extends AbstractController
{
    public const ROUTE_CREATE = '/token/create';
    public const ROUTE_VERIFY = '/token/verify';

    #[Route(self::ROUTE_CREATE, name: 'token_create')]
    public function create(): void
    {
    }

    #[Route(self::ROUTE_VERIFY, name: 'token_verify')]
    public function index(): Response
    {
        return new Response();
    }
}
