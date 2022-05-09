<?php

namespace Quiz\ORM\Builder;

interface DefinitionExtractorInterface
{
    public function extract(string $className);
}