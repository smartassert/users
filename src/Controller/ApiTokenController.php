<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class ApiTokenController
{
    public const ROUTE_CREATE = '/api/token/create';

    #[Route(self::ROUTE_CREATE, name: 'api_token_create')]
    public function create(): void
    {
    }
}
