<?php

namespace App\Representation;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class RepresentAs
{

    public function __construct(
        public readonly RepresentationType $type,

        /** Twig template that is going to be used for rendering. REQUIRED for TURBO and HTML type*/
        public readonly ?string $template = null,

        /** Frame for which rendering is going to happen. OPTIONAL, works only with TURBO type */
        public readonly ?string $turboFrame = null,

        /** Route to which user is going to be redirected. REQUIRED for FORM_SUBMITTED type*/
        public readonly ?string $redirectRoute = null,

        /** Route parameters that are cast to string to form redirect route. REQUIRED for FORM_SUBMITTED type*/
        public readonly array $routeParams = [],

        public readonly bool $cached = false,
    )
    {
    }

}
