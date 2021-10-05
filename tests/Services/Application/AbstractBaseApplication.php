<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use App\Tests\Services\ApplicationRoutes;

abstract class AbstractBaseApplication implements ApplicationInterface
{
    public function __construct(
        protected ClientInterface $client,
        protected ApplicationRoutes $routes,
    ) {
    }
}
