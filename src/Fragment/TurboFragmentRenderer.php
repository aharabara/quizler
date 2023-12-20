<?php
namespace App\Fragment;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer;
use Symfony\Component\HttpKernel\HttpCache\SubRequestHandler;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AutoconfigureTag('kernel.fragment_renderer', ['alias' => 'turbo'])]
class TurboFragmentRenderer extends InlineFragmentRenderer
{
    public function __construct(protected HttpKernelInterface $kernel, protected ?EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct($kernel, $dispatcher);
    }

    /**
     * Additional available options:
     *
     *  * alt: an alternative URI to render in case of an error
     */
    public function render(string|ControllerReference $uri, Request $request, array $options = []): Response
    {
        $frame = $options['frame'];
        if (!$frame) {
            throw new \InvalidArgumentException('Call to `render_turbo` requires a mandatory option `frame` which should tell which frame should be rendered');
        }

        $reference = null;
        if ($uri instanceof ControllerReference) {
            $reference = $uri;

            // Remove attributes from the generated URI because if not, the Symfony
            // routing system will use them to populate the Request attributes. We don't
            // want that as we want to preserve objects (so we manually set Request attributes
            // below instead)
            $attributes = $reference->attributes;
            $reference->attributes = [];

            // The request format and locale might have been overridden by the user
            foreach (['_format', '_locale'] as $key) {
                if (isset($attributes[$key])) {
                    $reference->attributes[$key] = $attributes[$key];
                }
            }

            $uri = $this->generateFragmentUri($uri, $request, false, false);

            $reference->attributes = array_merge($attributes, $reference->attributes);
        }

        $subRequest = $this->createSubRequest($uri, $request);
        $subRequest->headers->set('Turbo-Frame', $frame);
        $subRequest->attributes->set('_turbo', true);
        $subRequest->attributes->set('_turbo-frame', $frame);


        // override Request attributes as they can be objects (which are not supported by the generated URI)
        if (null !== $reference) {
            $subRequest->attributes->add($reference->attributes);
        }

        $level = ob_get_level();
        try {
            return SubRequestHandler::handle($this->kernel, $subRequest, HttpKernelInterface::SUB_REQUEST, false);
        } catch (\Exception $e) {
            // we dispatch the exception event to trigger the logging
            // the response that comes back is ignored
            if (isset($options['ignore_errors']) && $options['ignore_errors'] && $this->dispatcher) {
                $event = new ExceptionEvent($this->kernel, $request, HttpKernelInterface::SUB_REQUEST, $e);

                $this->dispatcher->dispatch($event, KernelEvents::EXCEPTION);
            }

            // let's clean up the output buffers that were created by the sub-request
            Response::closeOutputBuffers($level, false);

            if (isset($options['alt'])) {
                $alt = $options['alt'];
                unset($options['alt']);

                return $this->render($alt, $request, $options);
            }

            if (!isset($options['ignore_errors']) || !$options['ignore_errors']) {
                throw $e;
            }

            return new Response();
        }
    }

    public function getName(): string
    {
        return 'turbo';
    }
}
