<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\Frontend\RefreshToken;

use App\Tests\Application\Admin\Frontend\RefreshToken\AbstractRevokeAllForUserTestCase;
use App\Tests\Integration\Admin\GetAdminTokenTrait;
use App\Tests\Integration\GetClientAdapterTrait;

class RevokeAllForUserTest extends AbstractRevokeAllForUserTestCase
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
