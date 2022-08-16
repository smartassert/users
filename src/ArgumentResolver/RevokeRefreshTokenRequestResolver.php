<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\RevokeRefreshTokenRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RevokeRefreshTokenRequestResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return RevokeRefreshTokenRequest::class === $argument->getType();
    }

    /**
     * @return \Traversable<RevokeRefreshTokenRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Traversable
    {
        if ($this->supports($request, $argument)) {
            yield RevokeRefreshTokenRequest::create($request);
        }
    }
}
