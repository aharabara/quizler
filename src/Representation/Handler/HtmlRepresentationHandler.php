<?php

namespace App\Representation\Handler;

use App\Representation\RenderController;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

#[AsTaggedItem(100)]
class HtmlRepresentationHandler implements RepresentationHandler
{

    public function __construct(protected readonly RenderController $renderController)
    {
    }

    public function getType(): RepresentationType
    {
        return RepresentationType::HTML;
    }

    public function handle(Request $request, array $data, array $representations): Response
    {
        $attribute = $this->matchRepresentation($representations, $request);
        if (empty($attribute->template)) {
            $type = $this->getType()->value;
            throw new \InvalidArgumentException(
                "Parameter 'template' is required in order to render '{$type}' representation of this controller."
            );
        }

        $response = $this->renderController->render($attribute->template, $data);

        if ($attribute->cached) {
            $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        }

        return $response;
    }

    protected function matchRepresentation(array $representations, Request $request): RepresentAs
    {
        foreach ($representations as $representation) {
            /** @var RepresentAs $representation */
            if ($representation->type === $this->getType()) {
                return $representation;
            }
        }

        throw new \RuntimeException('Unsupported representation');
    }

    public function supports(Request $request, array $data, array $representations): bool
    {
        return true;
    }
}
