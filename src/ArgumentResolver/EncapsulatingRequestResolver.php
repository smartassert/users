<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\EncapsulatingRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class EncapsulatingRequestResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();
        if (null === $type) {
            return false;
        }

        if (false === class_exists($type)) {
            return false;
        }

        $implementedInterfaces = class_implements($type);
        if (!is_array($implementedInterfaces)) {
            return false;
        }

        return in_array(EncapsulatingRequestInterface::class, $implementedInterfaces);
    }

    /**
     * @return \Generator<EncapsulatingRequestInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $type = $argument->getType();
        if (is_string($type) && class_exists($type)) {
            yield new $type($request);
        }
    }
}
