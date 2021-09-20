<?php

declare(strict_types=1);

namespace App\Response;

class BadRequestValueMissingResponse extends BadRequestResponse
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('Value for field "%s" missing', $key));
    }
}
