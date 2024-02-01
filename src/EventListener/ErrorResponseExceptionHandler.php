<?php

declare(strict_types=1);

namespace App\EventListener;

use SmartAssert\ServiceRequest\Exception\ErrorResponseException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

readonly class ErrorResponseExceptionHandler implements EventSubscriberInterface
{
    /**
     * @return array<class-string, array<mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => [
                ['onKernelException', 100],
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if ($throwable instanceof ErrorResponseException) {
            $event->setResponse(new JsonResponse($throwable->error->serialize(), $throwable->getCode()));
            $event->stopPropagation();
        }
    }
}
