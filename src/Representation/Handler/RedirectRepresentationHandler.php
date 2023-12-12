<?php

namespace App\Representation\Handler;

use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsTaggedItem(110)]
class RedirectRepresentationHandler implements RepresentationHandler
{

    public function __construct(protected readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getType(): RepresentationType
    {
        return RepresentationType::REDIRECT;
    }

    public function handle(RepresentAs $attribute, Request $request, array $data): Response
    {
        $type = $this->getType()->value;
        if (empty($attribute->routeParams)) {
            throw new \InvalidArgumentException(
                "Parameter 'routeParams' is required in order to render '{$type}' representation of this controller."
            );
        }

        if (empty($attribute->redirectRoute)) {
            throw new \InvalidArgumentException(
                "Parameter 'redirectRoute' is required in order to render '{$type}' representation of this controller."
            );
        }


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
            $this->urlGenerator->generate($attribute->redirectRoute, $params),
            Response::HTTP_SEE_OTHER
        );
    }

    public function supports(Request $request, array $data): bool
    {
        return true;
    }
}
