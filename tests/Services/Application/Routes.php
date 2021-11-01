<?php

declare(strict_types=1);

namespace App\Tests\Services\Application;

class Routes
{
    public function __construct(
        public string $apiCreateTokenUrl,
        public string $apiVerifyTokenUrl,
        public string $frontendCreateTokenUrl,
        public string $frontendVerifyTokenUrl,
        public string $frontendRefreshTokenUrl,
        public string $adminCreateUserUrl,
        public string $adminRevokeRefreshTokenUrl,
        public string $healthCheckUrl,
    ) {
    }
}
