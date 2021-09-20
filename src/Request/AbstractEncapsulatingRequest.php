<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractEncapsulatingRequest implements EncapsulatingRequestInterface
{
    public function __construct(Request $request)
    {
        $this->processRequest($request);
    }
}
