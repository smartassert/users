<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

use App\Tests\Services\TestUserFactory;
use App\Tests\Services\UserRemover;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractTokenTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected TestUserFactory $testUserFactory;
    protected string $createUrl = '';
    protected string $verifyUrl = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $testUserFactory = self::getContainer()->get(TestUserFactory::class);
        \assert($testUserFactory instanceof TestUserFactory);
        $this->testUserFactory = $testUserFactory;

        $createUrl = self::getContainer()->getParameter($this->getCreateUrlParameter());
        if (is_string($createUrl)) {
            $this->createUrl = $createUrl;
        }

        $verifyUrl = self::getContainer()->getParameter($this->getVerifyUrlParameter());
        if (is_string($verifyUrl)) {
            $this->verifyUrl = $verifyUrl;
        }

        $this->removeAllUsers();
    }

    protected function tearDown(): void
    {
        $this->removeAllUsers();

        parent::tearDown();
    }

    abstract protected function getCreateUrlParameter(): string;

    abstract protected function getVerifyUrlParameter(): string;

    protected function removeAllUsers(): void
    {
        $userRemover = self::getContainer()->get(UserRemover::class);
        if ($userRemover instanceof UserRemover) {
            $userRemover->removeAll();
        }
    }
}
