<?php

namespace Quiz\Http\Listener;

use Quiz\Http\Controller\IndexController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouterListener implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        /* FIXME write a route matcher */
        $controller = match ($request->getMethod().' '.$request->getPathInfo()) {
            "GET /" => [IndexController::class, 'index'],
            "GET /question" => [IndexController::class, 'question'],
            "GET /answer" => [IndexController::class, 'answer'],
            default => [IndexController::class, 'debug']
        };

        $request->attributes->set('_controller', $controller);
//
//        $this->setCurrentRequest($request);
//
//        if ($request->attributes->has('_controller')) {
//            // routing is already done
//            return;
//        }
//
//        // add attributes based on the request (routing)
//        try {
//            // matching a request is more powerful than matching a URL path + context, so try that first
//            if ($this->matcher instanceof RequestMatcherInterface) {
//                $parameters = $this->matcher->matchRequest($request);
//            } else {
//                $parameters = $this->matcher->match($request->getPathInfo());
//            }
//
//            if (null !== $this->logger) {
//                $this->logger->info('Matched route "{route}".', [
//                    'route' => $parameters['_route'] ?? 'n/a',
//                    'route_parameters' => $parameters,
//                    'request_uri' => $request->getUri(),
//                    'method' => $request->getMethod(),
//                ]);
//            }
//
//            $request->attributes->add($parameters);
//            unset($parameters['_route'], $parameters['_controller']);
//            $request->attributes->set('_route_params', $parameters);
//        } catch (ResourceNotFoundException $e) {
//            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getUriForPath($request->getPathInfo()));
//
//            if ($referer = $request->headers->get('referer')) {
//                $message .= sprintf(' (from "%s")', $referer);
//            }
//
//            throw new NotFoundHttpException($message, $e);
//        } catch (MethodNotAllowedException $e) {
//            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getUriForPath($request->getPathInfo()), implode(', ', $e->getAllowedMethods()));
//
//            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
//        }
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        $request->attributes->set('_controller', [IndexController::class, 'failed']);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 32]],
            KernelEvents::FINISH_REQUEST => [['onKernelFinishRequest', 0]],
            KernelEvents::EXCEPTION => ['onKernelException', -64],
        ];
    }
}
