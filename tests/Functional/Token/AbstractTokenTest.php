<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

use App\Tests\Functional\AbstractBaseWebTestCase;
use App\Tests\Services\ApplicationResponseAsserter;

abstract class AbstractTokenTest extends AbstractBaseWebTestCase
{
    protected ApplicationResponseAsserter $applicationResponseAsserter;

    protected function setUp(): void
    {
        parent::setUp();

        $applicationResponseAsserter = self::getContainer()->get(ApplicationResponseAsserter::class);
        \assert($applicationResponseAsserter instanceof ApplicationResponseAsserter);
        $this->applicationResponseAsserter = $applicationResponseAsserter;
    }
}
