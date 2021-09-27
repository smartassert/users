<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use App\Security\AbstractUser;
use App\Security\UserRoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User extends AbstractUser implements UserInterface, PasswordAuthenticatedUserInterface, \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=UserPropertiesInterface::ID_LENGTH, unique=true)
     */
    protected string $id;

    /**
     * @ORM\Column(type="string", length=254, unique=true)
     */
    protected string $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected string $password;

    public function __construct(string $id, string $email, string $password)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;

        parent::__construct(
            $id,
            $email,
            [
                UserRoleInterface::ROLE_USER,
            ]
        );
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }
}
