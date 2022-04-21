<?php

namespace Quiz\Builder\SchemeBuilder;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class Key
{
    public function __construct()
    {
    }

}