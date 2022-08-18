<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Admin;

trait GetAdminTokenTrait
{
    protected function getAdminToken(): string
    {
        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        return $adminToken;
    }
}
