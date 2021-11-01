<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class StatusController
{
    public function get(): JsonResponse
    {
        return new JsonResponse([
            'idle' => true,
        ]);
    }
}
