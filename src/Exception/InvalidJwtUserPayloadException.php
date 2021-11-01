<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidJwtUserPayloadException extends \Exception implements InvalidJwtUserDataExceptionInterface
{
    public const MESSAGE = 'Payload key "%s" invalid';

    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        private mixed $username,
        private array $payload,
        private string $key,
    ) {
        parent::__construct(sprintf(self::MESSAGE, $key));
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

    public function getKey(): string
    {
        return $this->key;
    }
}
