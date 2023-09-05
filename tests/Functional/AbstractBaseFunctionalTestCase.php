<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Services\UserRemover;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractBaseFunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function tearDown(): void
    {
        $this->removeAllUsers();

        parent::tearDown();
    }

    protected function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }
    }
}
