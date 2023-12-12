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
        if ($throwable instanceof BadRequestHttpException) {
            if ('Invalid JSON.' === $throwable->getMessage()) {
                $event->setResponse(new UnauthorizedResponse());
                $event->stopPropagation();
            }
        }
    }
}
