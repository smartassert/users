<?php

declare(strict_types=1);

namespace App\Security\Api;

use App\Entity\User;
use App\Security\AudienceClaimInterface;
use App\Security\TokenInterface as JwtTokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as AuthenticationTokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class Authenticator extends AbstractAuthenticator
{
    public function __construct(
        private TokenExtractorInterface $tokenExtractor,
        private JWTTokenManagerInterface $JWTTokenManager,
        private AuthenticationSuccessHandler $successHandler,
    ) {
    }

    public function supports(Request $request): bool
    {
        return false !== $this->tokenExtractor->extract($request);
    }

    public function authenticate(Request $request): PassportInterface
    {
        $token = (string) $this->tokenExtractor->extract($request);

        return new SelfValidatingPassport(new UserBadge($token));
    }

    public function onAuthenticationSuccess(
        Request $request,
        AuthenticationTokenInterface $token,
        string $firewallName
    ): ?Response {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return $this->onAuthenticationFailure($request, new AuthenticationException());
        }

        $jwt = $this->JWTTokenManager->createFromPayload($user, [
            JwtTokenInterface::CLAIM_AUDIENCE => AudienceClaimInterface::AUD_API,
        ]);

        return $this->successHandler->handleAuthenticationSuccess($user, $jwt);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('', Response::HTTP_UNAUTHORIZED);
    }
}
