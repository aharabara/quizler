<?php

namespace App\Representation\Handler;

use App\Representation\RenderController;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function handle(RepresentAs $attribute, Request $request, array $data): Response
    {
        if (empty($attribute->template)) {
            $type = $this->getType()->value;
            throw new \InvalidArgumentException(
                "Parameter 'template' is required in order to render '{$type}' representation of this controller."
            );
        }

        return $this->renderController->render($attribute->template, $data);
    }

    public function supports(Request $request, array $data): bool
    {
        return true;
    }
}
