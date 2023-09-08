<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Frontend\ApiKey;

use App\Tests\Application\Frontend\ApiKey\AbstractGetDefaultTestCase;
use App\Tests\Functional\Application\Admin\GetAdminTokenTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class GetDefaultTest extends AbstractGetDefaultTestCase
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
