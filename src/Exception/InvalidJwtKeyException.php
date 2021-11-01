<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidJwtKeyException extends \Exception
{
    public const CODE_PUBLIC_KEY_NOT_A_STRING = 100;
    public const CODE_PRIVATE_KEY_NOT_A_STRING = 101;
    public const CODE_PUBLIC_KEY_INVALID = 200;
    public const CODE_PRIVATE_KEY_INVALID = 201;

    public function __construct(
        private string $type,
        string $message = '',
        int $code = 0
    ) {
        parent::__construct($message, $code);
    }

    public function getType(): string
    {
        return $this->type;
    }
}
