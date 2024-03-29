<?php

declare(strict_types=1);

namespace App\Security\Jwt;

use App\Exception\InvalidJwtAudException;
use App\Security\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class VerifyAuthenticator extends JWTAuthenticator
{
    /**
     * @param UserProviderInterface<UserInterface> $userProvider
     */
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        TokenExtractorInterface $tokenExtractor,
        UserProviderInterface $userProvider,
        private readonly FirewallMap $firewallMap
    ) {
        parent::__construct($jwtManager, $eventDispatcher, $tokenExtractor, $userProvider);
    }

    public function doAuthenticate(Request $request): Passport
    {
        $passport = parent::doAuthenticate($request);

        $firewallName = $this->getFirewallName($request);
        $jwtAud = $this->getJwtAud($passport);

        if (!is_string($firewallName) || !is_string($jwtAud) || !str_starts_with($firewallName, $jwtAud)) {
            throw new InvalidJwtAudException($firewallName, $jwtAud);
        }

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof JWTUser) {
            return parent::onAuthenticationFailure(
                $request,
                new InvalidTokenException()
            );
        }

        return new JsonResponse($user);
    }

    private function getFirewallName(Request $request): ?string
    {
        $firewallConfig = $this->firewallMap->getFirewallConfig($request);

        return $firewallConfig instanceof FirewallConfig ? $firewallConfig->getName() : null;
    }

    private function getJwtAud(Passport $passport): ?string
    {
        $payload = $passport->getAttribute('payload');
        if (!is_array($payload)) {
            return null;
        }

        if (!array_key_exists('aud', $payload)) {
            return null;
        }

        $aud = $payload['aud'];
        if (!is_array($aud)) {
            return null;
        }

        foreach ($aud as $audValue) {
            if (is_string($audValue)) {
                return $audValue;
            }
        }

        return null;
    }
}
