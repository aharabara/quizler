<?php

namespace Quiz\ORM\Scheme\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class Key
{
    public function __construct()
    {
    }
}
