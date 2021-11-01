<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security\Service;

use App\Security\Service\Authenticator;
use App\Tests\Functional\AbstractBaseFunctionalTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AuthenticatorTest extends AbstractBaseFunctionalTest
{
    private Authenticator $authenticator;
    private string $serviceToken;

    protected function setUp(): void
    {
        parent::setUp();

        $authenticator = self::getContainer()->get(Authenticator::class);
        \assert($authenticator instanceof Authenticator);
        $this->authenticator = $authenticator;

        $serviceToken = $this->getContainer()->getParameter('service-token');
        $this->serviceToken = is_string($serviceToken) ? $serviceToken : '';
    }

    /**
     * @dataProvider authenticateFailureDataProvider
     */
    public function testAuthenticateFailure(Request $request): void
    {
        self::expectExceptionObject(
            new CustomUserMessageAuthenticationException('Invalid token')
        );

        $this->authenticator->authenticate($request);
    }

    /**
     * @return array<mixed>
     */
    public function authenticateFailureDataProvider(): array
    {
        return [
            'no authorization header' => [
                'request' => new Request(),
            ],
            'invalid token' => [
                'request' => new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    [
                        'HTTP_AUTHORIZATION' => 'invalid-token',
                    ]
                ),
            ],
        ];
    }

    public function testAuthenticateSuccess(): void
    {
        $serviceTokens = [
            $this->serviceToken,
        ];

        foreach ($serviceTokens as $serviceToken) {
            $passport = $this->authenticator->authenticate(
                new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    [
                        'HTTP_AUTHORIZATION' => $serviceToken,
                    ]
                )
            );
            $expectedPassport = new SelfValidatingPassport(new UserBadge($serviceToken));

            self::assertEquals($expectedPassport, $passport);
        }
    }
}
