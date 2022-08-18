<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Api\Token;

use App\Tests\Application\Api\Token\AbstractCreateVerifyTest;
use App\Tests\Functional\Application\Admin\GetAdminTokenTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class CreateVerifyTest extends AbstractCreateVerifyTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
