<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

use Psr\Http\Message\ResponseInterface;

interface ApplicationInterface
{
    public function makeApiCreateTokenRequest(string $token): ResponseInterface;

    public function makeApiVerifyTokenRequest(?string $jwt): ResponseInterface;

    public function makeFrontendCreateTokenRequest(string $userIdentifier, string $password): ResponseInterface;

    public function makeFrontendVerifyTokenRequest(?string $jwt): ResponseInterface;

    public function makeFrontendRefreshTokenRequest(string $refreshToken): ResponseInterface;

    public function makeAdminCreateUserRequest(?string $email, ?string $password, ?string $adminToken): ResponseInterface;

    public function makeAdminRevokeRefreshTokenRequest(string $userId, string $adminToken): ResponseInterface;
}
