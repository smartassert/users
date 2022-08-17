<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Admin\Frontend\RefreshToken;

use App\Tests\Application\Admin\Frontend\RefreshToken\AbstractRevokeTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class RevokeTest extends AbstractRevokeTest
{
    use GetClientAdapterTrait;

    protected function getAdminToken(): string
    {
        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        return $adminToken;
    }
}
