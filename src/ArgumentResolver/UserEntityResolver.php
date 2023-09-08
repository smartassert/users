<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class UserEntityResolver implements ValueResolverInterface
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @return User[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (User::class !== $argument->getType()) {
            return [];
        }

        $user = $this->security->getUser();
        if (!$user instanceof UserInterface) {
            return [];
        }

        $userEntity = $this->userRepository->findByEmail($user->getUserIdentifier());

        return $userEntity instanceof User ? [$userEntity] : [];
    }
}
