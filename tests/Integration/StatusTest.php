<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractStatusTestCase;

class StatusTest extends AbstractStatusTestCase
{
    use GetClientAdapterTrait;

    protected function getExpectedReadyValue(): bool
    {
        return true;
    }
}
