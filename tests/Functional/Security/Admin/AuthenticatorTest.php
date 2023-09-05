<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security\Admin;

use App\Security\Admin\Authenticator;
use App\Tests\Functional\AbstractBaseFunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AuthenticatorTest extends AbstractBaseFunctionalTestCase
{
    private Authenticator $authenticator;
    private string $primaryAdminToken;
    private string $secondaryAdminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $authenticator = self::getContainer()->get(Authenticator::class);
        \assert($authenticator instanceof Authenticator);
        $this->authenticator = $authenticator;

        $primaryAdminToken = $this->getContainer()->getParameter('primary-admin-token');
        $this->primaryAdminToken = is_string($primaryAdminToken) ? $primaryAdminToken : '';

        $secondaryAdminToken = $this->getContainer()->getParameter('secondary-admin-token');
        $this->secondaryAdminToken = is_string($secondaryAdminToken) ? $secondaryAdminToken : '';
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
        $adminTokens = [
            $this->primaryAdminToken,
            $this->secondaryAdminToken
        ];

        foreach ($adminTokens as $adminToken) {
            $passport = $this->authenticator->authenticate(
                new Request(
                    [],
                    [],
                    [],
                    [],
                    [],
                    [
                        'HTTP_AUTHORIZATION' => $adminToken,
                    ]
                )
            );
            $expectedPassport = new SelfValidatingPassport(new UserBadge($adminToken));

            self::assertEquals($expectedPassport, $passport);
        }
    }
}
