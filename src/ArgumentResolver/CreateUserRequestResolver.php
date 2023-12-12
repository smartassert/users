<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\CreateUserRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CreateUserRequestResolver implements ValueResolverInterface
{
    /**
     * @return CreateUserRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (CreateUserRequest::class !== $argument->getType()) {
            return [];
        }

        $identifier = $request->request->get(CreateUserRequest::KEY_IDENTIFIER);
        $identifier = is_string($identifier) ? trim($identifier) : null;

        $password = $request->request->get(CreateUserRequest::KEY_PASSWORD);
        $password = is_string($password) ? $password : null;

        return [new CreateUserRequest('' === $identifier ? null : $identifier, '' === $password ? null : $password)];
    }
}
