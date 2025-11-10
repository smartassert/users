<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Security\IdentifiableUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class IdentifiableUserResolver implements ValueResolverInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * @return IdentifiableUserInterface[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (IdentifiableUserInterface::class !== $argument->getType()) {
            return [];
        }

        $user = $this->security->getUser();

        return $user instanceof IdentifiableUserInterface ? [$user] : [];
    }
}
