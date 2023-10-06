<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Frontend\RefreshToken;

use App\Tests\Application\Frontend\RefreshToken\AbstractRevokeTest;
use App\Tests\Functional\Application\Admin\GetAdminTokenTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class RevokeTest extends AbstractRevokeTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
