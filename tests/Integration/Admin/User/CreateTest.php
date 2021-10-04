<?php

declare(strict_types=1);

namespace App\Tests\Integration\Admin\User;

use App\Tests\Integration\AbstractIntegrationTest;

class CreateTest extends AbstractIntegrationTest
{
    /**
     * @dataProvider createUnauthorizedDataProvider
     */
    public function testCreateUnauthorized(?string $adminToken): void
    {
        $response = $this->application->makeAdminCreateUserRequest(
            self::TEST_USER_EMAIL,
            self::TEST_USER_EMAIL,
            $adminToken
        );

        $this->applicationResponseAsserter->assertAdminUnauthorizedResponse($response);
    }

    /**
     * @return array<mixed>
     */
    public function createUnauthorizedDataProvider(): array
    {
        return [
            'no credentials' => [
                'adminToken' => null,
            ],
            'invalid credentials' => [
                'adminToken' => 'invalid-token',
            ],
        ];
    }

    /**
     * @dataProvider createBadRequestDataProvider
     */
    public function testCreateBadRequest(?string $email, ?string $password): void
    {
        $adminToken = self::getContainer()->getParameter('primary-admin-token');
        \assert(is_string($adminToken));

        $response = $this->application->makeAdminCreateUserRequest($email, $password, $adminToken);

        self::assertSame(400, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadRequestDataProvider(): array
    {
        return [
            'no data' => [
                'email' => null,
                'password' => null,
            ],
            'email missing' => [
                'email' => null,
                'password' => self::TEST_USER_PASSWORD,
            ],
            'password missing' => [
                'email' => self::TEST_USER_EMAIL,
                'password' => null,
            ],
        ];
    }

    public function testCreateSuccess(): void
    {
        $this->removeAllUsers();

        $response = $this->createTestUser();

        $this->applicationResponseAsserter->assertCreateUserSuccessResponse($response, $this->getTestUser());
    }
}
