<?php

namespace App\Representation\Handler;

use App\Representation\RenderController;
use App\Representation\RepresentationType;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsTaggedItem(20)]
class TurboRepresentationHandler extends HtmlRepresentationHandler
{
    public function __construct(
        protected RequestStack $requestStack,
        RenderController       $renderController
    )
    {
        parent::__construct($renderController);
    }

    public function getType(): RepresentationType
    {
        return RepresentationType::TURBO;
    }

    public function supports(Request $request, array $data): bool
    {
        return $this->requestSupportsTurboStreams($request) || $this->requestStack->getMainRequest() !== $request;
    }

    public function requestSupportsTurboStreams(?Request $request): bool
    {
        return $request->attributes->get('_turbo');
    }
}
