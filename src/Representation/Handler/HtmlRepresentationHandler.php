<?php

namespace App\Representation\Handler;

use App\Representation\RenderController;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->renderController->render($attribute->template, $data);
    }
}
