<?php

namespace App\Representation\Handler;

use App\Representation\RenderController;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormSubmissionRepresentationHandler implements RepresentationHandler
{

    public function __construct(protected readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getType(): RepresentationType
    {
        return RepresentationType::FORM_SUBMITTED;
    }

    public function handle(RepresentAs $attribute, Request $request, array $data): Response
    {
        $params = [];
        foreach ($attribute->routeParams as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException(
                    "Route parameter '$key' is expected to be in the resulting array of the controller execution."
                );
            }
            $params[$key] = (string)$data[$key];
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('go_through_quiz', $params),
            Response::HTTP_SEE_OTHER
        );
    }
}
