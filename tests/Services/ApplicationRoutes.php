<?php

declare(strict_types=1);

namespace App\Tests\Services;

class ApplicationRoutes
{
    public function __construct(
        private string $apiCreateTokenUrl,
        private string $apiVerifyTokenUrl,
        private string $frontendCreateTokenUrl,
        private string $frontendVerifyTokenUrl,
        private string $frontendRefreshTokenUrl,
        private string $adminCreateUserUrl,
        private string $adminRevokeRefreshTokenUrl,
    ) {
    }

    public function getApiCreateTokenUrl(): string
    {
        return $this->apiCreateTokenUrl;
    }

    public function getApiVerifyTokenUrl(): string
    {
        return $this->apiVerifyTokenUrl;
    }

    public function getFrontendCreateTokenUrl(): string
    {
        return $this->frontendCreateTokenUrl;
    }

    public function getFrontendVerifyTokenUrl(): string
    {
        return $this->frontendVerifyTokenUrl;
    }

    public function getFrontendRefreshTokenUrl(): string
    {
        return $this->frontendRefreshTokenUrl;
    }

    public function getAdminCreateUserUrl(): string
    {
        return $this->adminCreateUserUrl;
    }

    public function getAdminRevokeRefreshTokenUrl(): string
    {
        return $this->adminRevokeRefreshTokenUrl;
    }
}
