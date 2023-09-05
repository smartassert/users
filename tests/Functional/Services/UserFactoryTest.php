<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Exception\UserAlreadyExistsException;
use App\Repository\UserRepository;
use App\Services\UserFactory;
use App\Tests\Functional\AbstractBaseFunctionalTestCase;
use Symfony\Component\Uid\Ulid;

class UserFactoryTest extends AbstractBaseFunctionalTestCase
{
    private UserFactory $userFactory;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $userFactory = self::getContainer()->get(UserFactory::class);
        \assert($userFactory instanceof UserFactory);
        $this->userFactory = $userFactory;

        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $this->removeAllUsers();
    }

    public function testCreate(): void
    {
        self::assertCount(0, $this->userRepository->findAll());

        $email = 'user@example.com';
        $plainPassword = 'password';

        $user = $this->userFactory->create($email, $plainPassword);

        self::assertNotEquals($plainPassword, $user->getPassword());
        self::assertTrue(Ulid::isValid($user->getId()));

        $retrievedUser = $this->userRepository->findByEmail($email);

        self::assertEquals($user, $retrievedUser);
    }

    public function testCreateUserAlreadyExists(): void
    {
        $email = 'user@example.com';
        $plainPassword = 'password';

        $user = $this->userFactory->create($email, $plainPassword);

        try {
            $this->userFactory->create($email, $plainPassword);
            $this->fail(UserAlreadyExistsException::class . ' not thrown');
        } catch (UserAlreadyExistsException $userAlreadyExistsException) {
            self::assertEquals($user, $userAlreadyExistsException->getUser());
        }
    }
}
