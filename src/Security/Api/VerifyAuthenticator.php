<?php

declare(strict_types=1);

namespace App\Security\Api;

use App\Security\IdentifiableUserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VerifyAuthenticator extends JWTAuthenticator
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof IdentifiableUserInterface) {
            return parent::onAuthenticationFailure(
                $request,
                new InvalidTokenException()
            );
        }

        return new Response(
            $user->getId(),
            200,
            [
                'content-type' => 'text/plain',
            ]
        );
    }
}
