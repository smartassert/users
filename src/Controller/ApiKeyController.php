<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyController
{
    public function list(): Response
    {
        return new JsonResponse([]);
    }
}
