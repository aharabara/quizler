<?php

namespace App\Representation;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class RepresentAs
{

    public function __construct(
        public readonly RepresentationType $type,

        /** Twig template that is going to be used for rendering. Required for TURBO and HTML type*/
        public readonly ?string $template = null,

        /** Route to which user is going to be redirected. Required for FORM_SUBMITTED type*/
        public readonly ?string $redirectRoute = null,

        /** Route parameters that are cast to string to form redirect route. Required for FORM_SUBMITTED type*/
        public readonly array $routeParams = [],
    )
    {
    }

}
