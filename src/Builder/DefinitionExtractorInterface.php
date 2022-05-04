<?php

namespace Quiz\Builder;

interface DefinitionExtractorInterface
{
    public function extract(string $className);
}