<?php

declare(strict_types=1);

namespace App\Controller;

use SmartAssert\ServiceStatusInspector\ServiceStatusInspectorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class HealthCheckController
{
    public function get(ServiceStatusInspectorInterface $serviceStatusInspector): JsonResponse
    {
        return new JsonResponse(
            $serviceStatusInspector->get(),
            $serviceStatusInspector->isAvailable() ? 200 : 503
        );
    }
}
