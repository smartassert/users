<?php

declare(strict_types=1);

namespace App\Exception;

interface InvalidJwtUserDataExceptionInterface extends \Throwable
{
    public function getUsername(): mixed;

    /**
     * @return array<mixed>
     */
    public function getPayload(): array;
}
