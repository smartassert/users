<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\UniqueConstraint(name: "owner_label_idx", columns: ['owner_id', 'label'])]
class ApiKey
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    public readonly User $owner;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: ApiKeyPropertiesInterface::ID_LENGTH, unique: true)]
    private readonly string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private readonly string $label;

    public function __construct(string $id, string $label, User $owner)
    {
        $this->id = $id;
        $this->label = $label;
        $this->owner = $owner;
    }
}
