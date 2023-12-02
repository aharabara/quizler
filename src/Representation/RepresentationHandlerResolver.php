<?php

namespace App\Representation;

use App\Representation\Handler\RepresentationHandler;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;

class RepresentationHandlerResolver
{

    public function __construct(
        #[TaggedIterator('representation.handler', 'index')] protected iterable $handlers
    )
    {
        $handlers = iterator_to_array($handlers);
        ksort($handlers);
        $this->handlers = $handlers;
    }

    public function getHandler(Request $request, array $data): ?RepresentationHandler
    {
        foreach ($this->handlers as $handler) {
            /** @var RepresentationHandler $handler */
            if ($handler->supports($request, $data)) {
                return $handler;
            }
        }

        return null;
    }
}
