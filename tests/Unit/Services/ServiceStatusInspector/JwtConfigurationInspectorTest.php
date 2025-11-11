<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\ServiceStatusInspector;

use App\Exception\InvalidJwtKeyException;
use App\Services\ServiceStatusInspector\JwtConfigurationInspector;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use PHPUnit\Framework\TestCase;

class JwtConfigurationInspectorTest extends TestCase
{
    /**
     * @dataProvider invokeFailureInvalidKeyDataProvider
     */
    public function testInvokeFailureInvalidKey(
        KeyLoaderInterface $keyLoader,
        \Exception $expectedException
    ): void {
        $encoder = \Mockery::mock(JWTEncoderInterface::class);

        $inspector = new JwtConfigurationInspector($encoder, $keyLoader);

        self::expectExceptionObject($expectedException);
        $inspector->getStatus();
    }

    /**
     * @return array<mixed>
     */
    public static function invokeFailureInvalidKeyDataProvider(): array
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
}
