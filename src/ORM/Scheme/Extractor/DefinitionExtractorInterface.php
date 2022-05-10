<?php

namespace Quiz\ORM\Scheme\Extractor;

interface DefinitionExtractorInterface
{
    public function extract(string $className);
}