<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\ApiKey;

use App\Tests\Application\Frontend\ApiKey\AbstractGetDefaultTestCase;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class GetDefaultTest extends AbstractGetDefaultTestCase
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
