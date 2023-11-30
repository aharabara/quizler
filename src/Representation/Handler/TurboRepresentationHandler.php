<?php

namespace App\Representation\Handler;

use App\Representation\RepresentationType;

class TurboRepresentationHandler extends HtmlRepresentationHandler
{

    public function getType(): RepresentationType
    {
        return RepresentationType::TURBO;
    }
}
