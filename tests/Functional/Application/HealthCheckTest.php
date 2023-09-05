<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractHealthCheckTestCase;

class HealthCheckTest extends AbstractHealthCheckTestCase
{
    use GetClientAdapterTrait;
}
