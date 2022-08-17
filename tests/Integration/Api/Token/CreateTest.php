<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api\Token;

use App\Tests\Application\Api\Token\AbstractCreateTest;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateTest extends AbstractCreateTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
