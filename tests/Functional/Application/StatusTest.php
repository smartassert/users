<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractStatusTest;

class StatusTest extends AbstractStatusTest
{
    use GetClientAdapterTrait;

    protected function getExpectedReadyValue(): bool
    {
        return false;
    }
}
