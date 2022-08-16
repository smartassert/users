<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\CreateUserRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CreateUserRequestResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return CreateUserRequest::class === $argument->getType();
    }

    /**
     * @return \Traversable<CreateUserRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Traversable
    {
        if ($this->supports($request, $argument)) {
            $email = $request->request->get(CreateUserRequest::KEY_EMAIL);
            $email = is_string($email) ? trim($email) : null;

            $password = $request->request->get(CreateUserRequest::KEY_PASSWORD);
            $password = is_string($password) ? $password : null;

            yield new CreateUserRequest('' === $email ? null : $email, '' === $password ? null : $password);
        }
    }
}
