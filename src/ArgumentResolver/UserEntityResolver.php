<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEntityResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return User::class === $argument->getType();
    }

    /**
     * @return \Traversable<?User>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Traversable
    {
        $user = $this->security->getUser();
        if ($user instanceof UserInterface) {
            $userEntity = $this->userRepository->findByEmail($user->getUserIdentifier());

            yield $userEntity instanceof User ? $userEntity : null;
        }

        yield null;
    }
}
