<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\User;

use App\Tests\Application\Admin\User\AbstractCreateTestCase;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateTest extends AbstractCreateTestCase
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
