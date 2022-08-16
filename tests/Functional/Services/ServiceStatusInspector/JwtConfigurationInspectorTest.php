<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services\ServiceStatusInspector;

use App\Exception\InvalidJwtKeyException;
use App\Services\ServiceStatusInspector\JwtConfigurationInspector;
use App\Tests\Functional\AbstractBaseFunctionalTest;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use webignition\ObjectReflector\ObjectReflector;

class JwtConfigurationInspectorTest extends AbstractBaseFunctionalTest
{
    private JwtConfigurationInspector $inspector;

    protected function setUp(): void
    {
        parent::setUp();

        $inspector = self::getContainer()->get(JwtConfigurationInspector::class);
        \assert($inspector instanceof JwtConfigurationInspector);
        $this->inspector = $inspector;
    }

    public function testInvokeSuccess(): void
    {
        $this->inspector->getStatus();
        self::expectNotToPerformAssertions();
    }

    /**
     * @dataProvider invokeFailureInvalidKeyDataProvider
     */
    public function testInvokeFailureInvalidKey(
        KeyLoaderInterface $keyLoader,
        \Exception $expectedException
    ): void {
        ObjectReflector::setProperty(
            $this->inspector,
            JwtConfigurationInspector::class,
            'keyLoader',
            $keyLoader
        );

        self::expectExceptionObject($expectedException);
        $this->inspector->getStatus();
    }

    /**
     * @return array<mixed>
     */
    public function invokeFailureInvalidKeyDataProvider(): array
    {
        return [
            'non-string public key' => [
                'keyLoader' => (function () {
                    $keyLoader = \Mockery::mock(KeyLoaderInterface::class);
                    $keyLoader
                        ->shouldReceive('loadKey')
                        ->with('public')
                        ->andReturn(null)
                    ;

                    return $keyLoader;
                })(),
                'expectedException' => new InvalidJwtKeyException(
                    'public',
                    '"public" key is not a string',
                    InvalidJwtKeyException::CODE_PUBLIC_KEY_NOT_A_STRING
                ),
            ],
            'invalid public key' => [
                'keyLoader' => (function () {
                    $keyLoader = \Mockery::mock(KeyLoaderInterface::class);
                    $keyLoader
                        ->shouldReceive('loadKey')
                        ->with('public')
                        ->andReturn('not a public key')
                    ;

                    return $keyLoader;
                })(),
                'expectedException' => new InvalidJwtKeyException(
                    'public',
                    '"public" key does not begin with "-----BEGIN PUBLIC KEY-----"',
                    InvalidJwtKeyException::CODE_PUBLIC_KEY_INVALID
                ),
            ],
            'non-string private key' => [
                'keyLoader' => (function () {
                    $keyLoader = \Mockery::mock(KeyLoaderInterface::class);
                    $keyLoader
                        ->shouldReceive('loadKey')
                        ->with('public')
                        ->andReturn("-----BEGIN PUBLIC KEY-----\ncontent")
                    ;

                    $keyLoader
                        ->shouldReceive('loadKey')
                        ->with('private')
                        ->andReturn(null)
                    ;

                    return $keyLoader;
                })(),
                'expectedException' => new InvalidJwtKeyException(
                    'private',
                    '"private" key is not a string',
                    InvalidJwtKeyException::CODE_PRIVATE_KEY_NOT_A_STRING
                ),
            ],
            'invalid private key' => [
                'keyLoader' => (function () {
                    $keyLoader = \Mockery::mock(KeyLoaderInterface::class);
                    $keyLoader
                        ->shouldReceive('loadKey')
                        ->with('public')
                        ->andReturn("-----BEGIN PUBLIC KEY-----\ncontent")
                    ;

                    $keyLoader
                        ->shouldReceive('loadKey')
                        ->with('private')
                        ->andReturn('not a private key')
                    ;

                    return $keyLoader;
                })(),
                'expectedException' => new InvalidJwtKeyException(
                    'private',
                    '"private" key does not begin with "-----BEGIN ENCRYPTED PRIVATE KEY-----"',
                    InvalidJwtKeyException::CODE_PRIVATE_KEY_INVALID
                ),
            ],
        ];
    }

    public function testInvokeEncodeFailure(): void
    {
        $encoderException = new JWTEncodeFailureException(JWTEncodeFailureException::INVALID_CONFIG, '', null, []);

        $encoder = \Mockery::mock(JWTEncoderInterface::class);
        $encoder
            ->shouldReceive('encode')
            ->andThrow($encoderException)
        ;

        ObjectReflector::setProperty(
            $this->inspector,
            JwtConfigurationInspector::class,
            'jwtEncoder',
            $encoder
        );

        self::expectExceptionObject($encoderException);

        $this->inspector->getStatus();
    }
}
