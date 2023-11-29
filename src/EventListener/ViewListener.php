<?php

namespace App\EventListener;

use App\Entity\Question;
use App\Representation\Handler\ChainRepresentationHandler;
use App\Representation\Handler\RepresentationHandler;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\PostPersist;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewListener
{
    public function __construct(
        protected readonly ChainRepresentationHandler $handler
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

        $form = $this->thereIsASubmittedForm($event->getControllerResult());

        $type = RepresentationType::HTML;
        if ($form && $form->isValid()){
            $type = RepresentationType::FORM_SUBMITTED;
        } elseif ($this->requestSupportsTurboStreams($event->getRequest()) || !$event->isMainRequest()) {
            $type = RepresentationType::TURBO;
        }

        $representation = $this->getMatchingRepresentation($type, $representations);

        $response = $this->handler->handle($representation, $event->getRequest(), $event->getControllerResult());

        $event->setResponse($response);
    }

    private function getMatchingRepresentation(RepresentationType $type, array $representations): RepresentAs
    {
        foreach ($representations as $representation) {
            /** @var RepresentAs $representation */
            if ($representation->type === $type) {
                return $representation;
            }
        }
        throw new \RuntimeException('Unsupported representation');
    }

    public function requestSupportsTurboStreams(?Request $request): bool
    {
        return str_contains($request->headers->get('Accept'), "text/vnd.turbo-stream.html");
    }

    private function thereIsASubmittedForm(array $parameters): ?FormInterface
    {
        foreach ($parameters as $v) {
            if ($v instanceof FormInterface && $v->isSubmitted()) {
                return $v;
            }
        }

        return null;
    }

}
