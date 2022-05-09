<?php

namespace Quiz\ORM\Builder\SchemeBuilder;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique extends Key
{
    public function __construct()
    {
        parent::__construct();
    }

}