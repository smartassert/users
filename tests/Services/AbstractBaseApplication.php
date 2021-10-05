<?php

declare(strict_types=1);

namespace App\Tests\Services;

abstract class AbstractBaseApplication
{
    public function __construct(
        protected ApplicationRoutes $routes,
    ) {
    }
}
