<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractHealthCheckTestCase;

class HealthCheckTest extends AbstractHealthCheckTestCase
{
    use GetClientAdapterTrait;
}
