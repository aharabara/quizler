<?php

namespace Quiz\Builder\SchemeBuilder;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Relation extends Key
{
    public function __construct(
        protected string $class
    )
    {
        if (!class_exists($class)){
            throw new \InvalidArgumentException('Relation requires an existing class.');
        }
        parent::__construct();
    }

}