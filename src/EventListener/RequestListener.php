<?php

namespace App\EventListener;

use App\Representation\RepresentationHandlerResolver;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener
{
    public function __construct(
        protected readonly RepresentationHandlerResolver $handler
    )
    {
    }

    #[AsEventListener(KernelEvents::REQUEST)]
    public function onRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        $request->attributes->set(
            '_turbo',
            $request->headers->has('Turbo-Frame')
        );

        if ($request->headers->has('Turbo-Frame')) {
            $request->attributes->set('_turbo-frame', $request->headers->get('Turbo-Frame'));
        }

    }
}
