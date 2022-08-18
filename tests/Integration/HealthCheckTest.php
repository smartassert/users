<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractHealthCheckTest;

class HealthCheckTest extends AbstractHealthCheckTest
{
    use GetClientAdapterTrait;
}
