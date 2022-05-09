<?php

namespace Quiz\ORM\Builder\SchemeBuilder;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Identificator extends Key
{
    public function __construct(protected bool $autoincrement = true)
    {
        parent::__construct();
    }

    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

}