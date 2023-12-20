<?php

namespace App\EventListener;

use App\Representation\RepresentAs;
use App\Representation\RepresentationHandlerResolver;
use App\Representation\RepresentationType;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewListener
{
    public function __construct(
        protected readonly RepresentationHandlerResolver $handler
    )
    {
    }

    #[AsEventListener(KernelEvents::VIEW)]
    public function onViewEvent(ViewEvent $event): void
    {
        $controllerCallable = $event->controllerArgumentsEvent->getController();

        if (!is_array($controllerCallable)) {
            throw new \RuntimeException('Controller not supported for representation' . json_encode($controllerCallable));
        }
        [$controller, $method] = array_pad($controllerCallable, 2, '__invoke');

        $refMethod = new \ReflectionMethod($controller, $method);

        $representations = array_map(
            fn(\ReflectionAttribute $refAttribute) => $refAttribute->newInstance(),
            $refMethod->getAttributes(RepresentAs::class)
        );

        if (empty($representations)) {
            // this controller has no representations
            return;
        }

        $handler = $this->handler->getHandler($event->getRequest(), $event->getControllerResult(), $representations);

        if (!$handler) {
            // this controller in current state cannot be handled by any handler
            return;
        }

        $event->setResponse(
            $handler->handle($event->getRequest(), $event->getControllerResult(), $representations)
        );
    }
}
