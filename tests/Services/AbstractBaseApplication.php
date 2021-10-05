<?php

declare(strict_types=1);

namespace App\Tests\Services;

abstract class AbstractBaseApplication implements ApplicationInterface
{
    public function __construct(
        protected ApplicationRoutes $routes,
    ) {
    }
}
