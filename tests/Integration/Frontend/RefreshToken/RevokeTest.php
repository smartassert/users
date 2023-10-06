<?php

declare(strict_types=1);

namespace App\Tests\Integration\Frontend\RefreshToken;

use App\Tests\Application\Frontend\RefreshToken\AbstractRevokeTest;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class RevokeTest extends AbstractRevokeTest
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
