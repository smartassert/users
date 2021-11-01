<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidJwtUserUsernameException extends \Exception implements InvalidJwtUserDataExceptionInterface
{
    public const MESSAGE = 'Username argument is not a string. Is type: "%s"';

    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        private mixed $username,
        private array $payload,
    ) {
        parent::__construct(sprintf(self::MESSAGE, gettype($username)));
    }

    public function getUsername(): mixed
    {
        return $this->username;
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
