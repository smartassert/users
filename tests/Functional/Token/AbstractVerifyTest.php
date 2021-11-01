<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

abstract class AbstractVerifyTest extends AbstractTokenTest
{
    /**
     * @return array<mixed>
     */
    public function verifyUnauthorizedDataProvider(): array
    {
        return [
            'no jwt' => [
                'token' => null,
            ],
            'malformed jwt' => [
                'token' => 'malformed.jwt.token',
            ],
            'invalid jwt' => [
                'token' => 'eyJhbGciOiJIUzI1NiJ9.e30.ZRrHA1JJJW8opsbCGfG_HACGpVUMN_a9IV7pAx_Zmeo',
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function verifyInvalidUserDataDataProvider(): array
    {
        return [
            'empty token data' => [
                'tokenData' => [],
                'expectedResponseStatusCode' => 401,
                'expectedResponseBodyContains' => 'Unable to find key \u0022email\u0022 in the token payload.',
            ],
            'payload sub key missing' => [
                'tokenData' => [
                    'email' => 'user@example.com',
                ],
                'expectedResponseStatusCode' => 500,
                'expectedResponseBodyContains' => 'Payload key &quot;sub&quot; invalid',
            ],
            'payload roles key missing' => [
                'tokenData' => [
                    'email' => 'user@example.com',
                    'sub' => 'user@example.com',
                ],
                'expectedResponseStatusCode' => 500,
                'expectedResponseBodyContains' => 'Payload key &quot;roles&quot; invalid',
            ],
        ];
    }
}
