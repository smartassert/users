<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\RevokeRefreshTokenRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RevokeRefreshTokenRequestResolver implements ValueResolverInterface
{
    /**
     * @return RevokeRefreshTokenRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (RevokeRefreshTokenRequest::class !== $argument->getType()) {
            return [];
        }

        $id = $request->request->get(RevokeRefreshTokenRequest::KEY_ID);
        $id = is_string($id) ? trim($id) : null;
        $id = '' === $id ? null : $id;

        return [new RevokeRefreshTokenRequest($id)];
    }
}
