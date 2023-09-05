<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api\Token;

use App\Tests\Application\Api\Token\AbstractCreateVerifyTestCase;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateVerifyTest extends AbstractCreateVerifyTestCase
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
