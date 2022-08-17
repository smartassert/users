<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\Frontend\RefreshToken;

use App\Tests\Application\Admin\Frontend\RefreshToken\AbstractRevokeTest;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class RevokeTest extends AbstractRevokeTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
