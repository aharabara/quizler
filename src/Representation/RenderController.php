<?php

namespace App\Representation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class RenderController extends AbstractController{

    /*@note make it public, so it can be reused for rendering logic in the RepresentationHandlers */
    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, $parameters, $response);
    }
}
