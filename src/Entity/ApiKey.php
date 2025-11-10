<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\UniqueConstraint(name: 'owner_label_idx', columns: ['owner_id', 'label'])]
readonly class ApiKey
{
    #[ORM\Column(nullable: false)]
    public string $ownerId;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: ApiKeyPropertiesInterface::ID_LENGTH, unique: true)]
    public string $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $label;

    /**
     * @param non-empty-string  $id
     * @param ?non-empty-string $label
     */
    public function __construct(string $id, ?string $label, string $ownerId)
    {
        $this->id = $id;
        $this->label = $label;
        $this->ownerId = $ownerId;
    }
}
