<?php

declare(strict_types=1);

namespace GalDigitalGmbh\Validation\Symfony\EventSubscriber;

use GalDigitalGmbh\Validation\Symfony\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ValidationExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (!$event->isMainRequest() || (!$throwable instanceof ValidationException)) {
            return;
        }

        $response = (new JsonResponse())
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->setData([
                'message' => 'Validation error',
                'errors'  => $throwable->getViolationMessages(),
            ])
        ;

        $event->setResponse($response);
    }
}
