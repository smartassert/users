<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\RevokeRefreshTokenRequest;
use SmartAssert\ServiceRequest\Exception\ErrorResponseException;
use SmartAssert\ServiceRequest\Parameter\Factory;
use SmartAssert\ServiceRequest\Parameter\Validator\StringParameterValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class RevokeRefreshTokenRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private StringParameterValidator $parameterValidator,
        private Factory $parameterFactory,
    ) {
    }

    /**
     * @return RevokeRefreshTokenRequest[]
     *
     * @throws ErrorResponseException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (RevokeRefreshTokenRequest::class !== $argument->getType()) {
            return [];
        }

        $id = $this->parameterValidator->validateNonEmptyString($this->parameterFactory->createStringParameter(
            RevokeRefreshTokenRequest::KEY_ID,
            trim($request->request->getString(RevokeRefreshTokenRequest::KEY_ID)),
            1,
            null,
        ));

        return [new RevokeRefreshTokenRequest($id)];
    }
}
