<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApiKeyRepository::class)
 */
class ApiKey
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=ApiKeyPropertiesInterface::ID_LENGTH, unique=true)
     */
    private string $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $label;

    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
