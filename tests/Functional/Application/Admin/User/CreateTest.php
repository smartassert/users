<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Admin\User;

use App\Tests\Application\Admin\User\AbstractCreateTest;
use App\Tests\Functional\Application\Admin\GetAdminTokenTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class CreateTest extends AbstractCreateTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
