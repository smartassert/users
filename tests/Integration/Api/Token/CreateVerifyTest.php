<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api\Token;

use App\Tests\Application\Api\Token\AbstractCreateVerifyTest;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateVerifyTest extends AbstractCreateVerifyTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
