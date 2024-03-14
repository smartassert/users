<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Response\UnauthorizedResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthenticationInvalidJsonResponseConverter implements EventSubscriberInterface
{
    /**
     * @var non-empty-string[]
     */
    private array $expectedExceptionMessages = [
        'Invalid JSON.',
        'The key "username" must be ',
        'The key "password" must be ',
    ];

    /**
     * @return array<class-string, array<mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => [
                ['convertInvalidJsonBadRequestResponseToUnauthorizedResponse', 100],
            ],
        ];
    }

    public function convertInvalidJsonBadRequestResponseToUnauthorizedResponse(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof BadRequestHttpException) {
            return;
        }

        $message = $throwable->getMessage();

        foreach ($this->expectedExceptionMessages as $expectedExceptionMessage) {
            if (str_starts_with($message, $expectedExceptionMessage)) {
                $event->setResponse(new UnauthorizedResponse());
                $event->stopPropagation();
            }
        }
    }
}
