<?php

namespace App\Representation\Handler;

use RuntimeException;
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
        if ($this->isMainRequest($request)){
            return $this->supportsTurbo($request) && $this->hasMatchingRepresention($representations, $request);
        }

        // because we want to see if it fails in the nested requests
        return $this->supportsTurbo($request);
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

    public function getMatchingRepresentation(array $representations, string $frameName): ?RepresentAs
    {
        foreach ($representations as $representation) {
            /** @var RepresentAs $representation */
            if ($representation->type !== RepresentationType::TURBO) continue;
            if ($representation->turboFrame === null) continue;
            if ($representation->turboFrame === $frameName) {
                return $representation;
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isMainRequest(Request $request): bool
    {
        return $this->requestStack->getMainRequest() === $request;
    }

    /**
     * @param array $representations
     * @param Request $request
     * @return bool
     */
    public function hasMatchingRepresention(array $representations, Request $request): bool
    {
        return $this->getMatchingRepresentation($representations, $request->attributes->get('_turbo-frame')) !== null;
    }

    protected function matchRepresentation(array $representations, Request $request): RepresentAs
    {
        $frameName = $request->attributes->get('_turbo-frame');
        $representAs = $this->getMatchingRepresentation($representations, $frameName);

        if ($representAs === null) {
            throw new RuntimeException("There is no matching turbo representation for '{$request->attributes->get('_route')}' endpoint for '$frameName' frame.");
        }

        return $representAs;
    }
}
