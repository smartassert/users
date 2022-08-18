<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\Token;

use App\Tests\Application\Frontend\Token\AbstractCreateVerifyRefreshTest;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateVerifyRefreshTest extends AbstractCreateVerifyRefreshTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
