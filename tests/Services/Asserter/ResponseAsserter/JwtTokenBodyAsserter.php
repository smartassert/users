<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use App\Tests\Services\Asserter\AssociativeArrayAsserter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Assert;

class JwtTokenBodyAsserter extends ArrayBodyAsserter implements BodyAsserterInterface
{
    private JWTTokenManagerInterface $JWTTokenManager;
//    private AssociativeArrayAsserter $arrayDataAsserter;
    private string $expectedTokenKey;
    private AssociativeArrayAsserter $payloadAsserter;

    /**
     * @param array<string, null|bool|int|string> $expectedPayload
     */
    public function __construct(
        JWTTokenManagerInterface $JWTTokenManager,
        string $expectedTokenKey,
        array $expectedPayload
    ) {
        $this->JWTTokenManager = $JWTTokenManager;
        $this->expectedTokenKey = $expectedTokenKey;
        $this->payloadAsserter = (new AssociativeArrayAsserter($expectedPayload));

        parent::__construct([
            $expectedTokenKey => null,
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function assert(string $body): array
    {
        $data = parent::assert($body);
        $token = $data[$this->expectedTokenKey];

        $payload = $this->JWTTokenManager->parse($token);
        Assert::assertIsArray($payload);

        $this->payloadAsserter->assert($payload);

        return $payload;
//
//        (new AssociativeArrayAsserter([
//            TokenInterface::CLAIM_EMAIL => $user->getUserIdentifier(),
//            TokenInterface::CLAIM_USER_ID => $user->getId(),
//        ]))->assert($payload);

//        $data = json_decode($body, true);
//        Assert::assertIsArray($data);
//        $this->arrayDataAsserter->assert($data);
//
//        $responseData = json_decode((string) $response->getContent(), true);
//        $token = $responseData['token'];
//
//        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
//        \assert($jwtManager instanceof JWTTokenManagerInterface);
//
//        $payload = $jwtManager->parse($token);
//
//        self::assertIsArray($payload);
//
//        (new AssociativeArrayAsserter([
//            TokenInterface::CLAIM_EMAIL => $user->getUserIdentifier(),
//            TokenInterface::CLAIM_USER_ID => $user->getId(),
//        ]))->assert($payload);
    }
}
