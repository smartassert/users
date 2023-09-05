<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Frontend\ApiKey;

use App\Tests\Application\Frontend\ApiKey\AbstractListTestCase;
use App\Tests\Functional\Application\Admin\GetAdminTokenTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class ListTest extends AbstractListTestCase
{
    use GetClientAdapterTrait;
    use GetAdminTokenTrait;
}
