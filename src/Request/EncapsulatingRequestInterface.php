<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

interface EncapsulatingRequestInterface
{
    public function processRequest(Request $request): void;
}
