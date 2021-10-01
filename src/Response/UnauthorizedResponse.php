<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\Response;

class UnauthorizedResponse extends Response
{
    public function __construct()
    {
        parent::__construct(null, Response::HTTP_UNAUTHORIZED);
    }
}
