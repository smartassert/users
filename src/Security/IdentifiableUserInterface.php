<?php

declare(strict_types=1);

namespace App\Security;

interface IdentifiableUserInterface
{
    public function getId(): string;
}
