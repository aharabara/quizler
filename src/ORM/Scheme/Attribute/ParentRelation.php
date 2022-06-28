<?php

namespace Quiz\ORM\Scheme\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ParentRelation extends Relation
{
}
