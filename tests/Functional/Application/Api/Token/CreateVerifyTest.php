<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Api\Token;

use App\Tests\Application\Api\Token\AbstractCreateVerifyTestCase;
use App\Tests\Functional\Application\Admin\GetAdminTokenTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class CreateVerifyTest extends AbstractCreateVerifyTestCase
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
