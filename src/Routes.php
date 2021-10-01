<?php

declare(strict_types=1);

namespace App;

class Routes
{
    public const ROUTE_FRONTEND_TOKEN_CREATE = '/frontend/token/create';
    public const ROUTE_FRONTEND_TOKEN_VERIFY = '/frontend/token/verify';
    public const ROUTE_FRONTEND_TOKEN_REFRESH = '/frontend/token/refresh';
    public const ROUTE_API_TOKEN_CREATE = '/api/token/create';
    public const ROUTE_API_TOKEN_VERIFY = '/api/token/verify';

    public const PREFIX_ADMIN = '/admin';
    public const ROUTE_ADMIN_USER_CREATE = self::PREFIX_ADMIN . '/user/create';
    public const ROUTE_ADMIN_FRONTEND_REFRESH_TOKEN_REVOKE = self::PREFIX_ADMIN . '/frontend/refresh-token/revoke';
}
