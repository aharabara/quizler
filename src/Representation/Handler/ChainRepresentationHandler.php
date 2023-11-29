<?php

namespace App\Representation\Handler;

use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChainRepresentationHandler implements RepresentationHandler
{

    public function __construct(
        #[TaggedIterator('representation.handler')] protected readonly iterable $handlers
    )
    {
    }

    public function getType(): RepresentationType
    {
        return RepresentationType::HTML; // return default
    }

    public function handle(RepresentAs $attribute, Request $request, array $data): Response
    {
        foreach ($this->handlers as $handler) {
            /** @var RepresentationHandler $handler */
            if ($handler->getType() === $attribute->type){
                return $handler->handle($attribute, $request, $data);
            }
        }
        throw new \RuntimeException('Unsupported handler');
    }
}
