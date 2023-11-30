<?php

namespace App\Representation;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class RepresentAs
{

    public function __construct(
        public readonly RepresentationType $type,
        public readonly ?string $template = null,
        public readonly ?string $redirectRoute = null,
        public readonly array $routeParams = [],
    )
    {
    }

}
