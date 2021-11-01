<?php

declare(strict_types=1);

namespace App\Security\Service;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class Authenticator extends AbstractAuthenticator
{
    /**
     * @param string[] $validTokens
     */
    public function __construct(
        private TokenExtractorInterface $tokenExtractor,
        private array $validTokens
    ) {
    }

    public function supports(Request $request): bool
    {
        return false !== $this->tokenExtractor->extract($request);
    }

    public function authenticate(Request $request): PassportInterface
    {
        $token = (string) $this->tokenExtractor->extract($request);

        if (!in_array($token, $this->validTokens)) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        return new SelfValidatingPassport(new UserBadge($token));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('', Response::HTTP_UNAUTHORIZED);
    }
}
