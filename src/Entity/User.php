<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use App\Security\AbstractUser;
use App\Security\UserRoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User extends AbstractUser implements UserInterface, PasswordAuthenticatedUserInterface, \JsonSerializable
{
    public const ROLES = [
        UserRoleInterface::ROLE_USER,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: UserPropertiesInterface::ID_LENGTH, unique: true)]
    protected string $id;

    #[ORM\Column(type: 'string', length: UserPropertiesInterface::IDENTIFIER_MAX_LENGTH, unique: true)]
    protected string $userIdentifier;

    #[ORM\Column(type: 'string')]
    protected string $password;

    /**
     * @var array<UserRoleInterface::ROLE_*>
     */
    protected array $roles = self::ROLES;

    public function __construct(string $id, string $userIdentifier, string $password)
    {
        $this->id = $id;
        $this->userIdentifier = $userIdentifier;
        $this->password = $password;

        parent::__construct(
            $id,
            $userIdentifier,
            self::ROLES
        );
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
