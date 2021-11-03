<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class StatusController
{
    public function __construct(
        private bool $isReady,
    ) {
    }

    public function get(): JsonResponse
    {
        return new JsonResponse([
            'idle' => true,
            'ready' => $this->isReady,
        ]);
    }
}
