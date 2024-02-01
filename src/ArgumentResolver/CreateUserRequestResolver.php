<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Entity\UserPropertiesInterface;
use App\Request\CreateUserRequest;
use SmartAssert\ServiceRequest\Exception\ErrorResponseException;
use SmartAssert\ServiceRequest\Parameter\Factory;
use SmartAssert\ServiceRequest\Parameter\Validator\StringParameterValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class CreateUserRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private StringParameterValidator $parameterValidator,
        private Factory $parameterFactory,
    ) {
    }

    /**
     * @return CreateUserRequest[]
     *
     * @throws ErrorResponseException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (CreateUserRequest::class !== $argument->getType()) {
            return [];
        }

        $identifier = $this->parameterValidator->validateNonEmptyString($this->parameterFactory->createStringParameter(
            CreateUserRequest::KEY_IDENTIFIER,
            trim($request->request->getString(CreateUserRequest::KEY_IDENTIFIER)),
            1,
            UserPropertiesInterface::IDENTIFIER_MAX_LENGTH,
        ));

        $password = $this->parameterValidator->validateNonEmptyString($this->parameterFactory->createStringParameter(
            CreateUserRequest::KEY_PASSWORD,
            trim($request->request->getString(CreateUserRequest::KEY_PASSWORD)),
            1,
            null
        ));

        return [new CreateUserRequest($identifier, $password)];
    }
}
