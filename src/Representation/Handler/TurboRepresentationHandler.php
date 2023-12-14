<?php

namespace App\Representation\Handler;

use App\Representation\RenderController;
use App\Representation\RepresentAs;
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

    public function supports(Request $request, array $data, array $representations): bool
    {
        if (!$this->supportsTurbo($request) && $this->requestStack->getMainRequest() === $request) return false;

        $frameName = $request->attributes->get('_turbo-frame');

        return $this->getMatchingRepresentation($representations, $frameName) !== null;
    }

    public function supportsTurbo(?Request $request): bool
    {
        return $request->attributes->get('_turbo');
    }

    /**
     * @param array $representations
     * @return RepresentAs|null
     */
    public function getDefaultTurboRepresentation(array $representations): ?RepresentAs
    {
        foreach ($representations as $representation) {
            /** @var RepresentAs $representation */
            if ($representation->type !== RepresentationType::TURBO) continue;
            if ($representation->turboFrame === null) {
                return $representation;
            }
        }

        return null;
    }

    public function getMatchingRepresentation(array $representations, ?string $frameName): ?RepresentAs
    {
        if ($frameName === null){
            return $this->getDefaultTurboRepresentation($representations);
        }

        foreach ($representations as $representation) {
            /** @var RepresentAs $representation */
            if ($representation->type !== RepresentationType::TURBO) continue;
            if ($representation->turboFrame === null) continue;
            if ($representation->turboFrame === $frameName) {
                return $representation;
            }
        }

        return $this->getDefaultTurboRepresentation($representations);
    }

    protected function matchRepresentation(array $representations, Request $request): RepresentAs
    {
        return $this->getMatchingRepresentation($representations, $request->attributes->get('_turbo-frame'));
    }
}
