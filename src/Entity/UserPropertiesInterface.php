<?php

declare(strict_types=1);

namespace App\Entity;

interface UserPropertiesInterface
{
    public const ID_LENGTH = 32;
    public const IDENTIFIER = 'userIdentifier';

    public const IDENTIFIER_MAX_LENGTH = 254;
}
