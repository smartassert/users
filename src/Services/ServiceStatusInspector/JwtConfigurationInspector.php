<?php

declare(strict_types=1);

namespace App\Services\ServiceStatusInspector;

use App\Exception\InvalidJwtKeyException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;

class JwtConfigurationInspector
{
    public function __construct(
        private JWTEncoderInterface $jwtEncoder,
        private KeyLoaderInterface $keyLoader,
    ) {
    }

    /**
     * @throws InvalidJwtKeyException
     * @throws JWTEncodeFailureException
     */
    public function __invoke(): void
    {
        $this->verifyKeyContent(
            'public',
            '-----BEGIN PUBLIC KEY-----',
            InvalidJwtKeyException::CODE_PUBLIC_KEY_NOT_A_STRING,
            InvalidJwtKeyException::CODE_PUBLIC_KEY_INVALID
        );
        $this->verifyKeyContent(
            'private',
            '-----BEGIN ENCRYPTED PRIVATE KEY-----',
            InvalidJwtKeyException::CODE_PRIVATE_KEY_NOT_A_STRING,
            InvalidJwtKeyException::CODE_PRIVATE_KEY_INVALID
        );

        $this->jwtEncoder->encode([]);
    }

    /**
     * @throws InvalidJwtKeyException
     */
    private function verifyKeyContent(
        string $type,
        string $expectedFirstLine,
        int $keyNotAStringExceptionCode,
        int $keyInvalidExceptionCode
    ): void {
        $key = $this->keyLoader->loadKey($type);
        if (false === is_string($key)) {
            throw new InvalidJwtKeyException(
                $type,
                sprintf('"%s" key is not a string', $type),
                $keyNotAStringExceptionCode
            );
        }

        if (false === str_starts_with($key, $expectedFirstLine . "\n")) {
            throw new InvalidJwtKeyException(
                $type,
                sprintf('"%s" key does not begin with "%s"', $type, $expectedFirstLine),
                $keyInvalidExceptionCode
            );
        }
    }
}
