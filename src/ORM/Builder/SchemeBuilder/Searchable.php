<?php

namespace Quiz\ORM\Builder\SchemeBuilder;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Searchable extends Key
{
}