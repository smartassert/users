<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Frontend\Token;

use App\Tests\Application\Frontend\Token\AbstractCreateVerifyRefreshTest;
use App\Tests\Functional\Application\Admin\GetAdminTokenTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class CreateVerifyRefreshTest extends AbstractCreateVerifyRefreshTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
