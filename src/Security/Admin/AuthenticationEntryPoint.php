<?php

declare(strict_types=1);

namespace App\Security\Admin;

use App\Response\UnauthorizedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new UnauthorizedResponse();
    }
}
