<?php

namespace Quiz\ORM\Builder;

class CachedDefinitionExtractor implements DefinitionExtractorInterface
{
    private array $cache;

    public function __construct(protected DefinitionExtractorInterface $extractor)
    {
    }

    public function extract(string $className)
    {
        if (!isset($this->cache[$className])){
            $this->cache[$className] = $this->extractor->extract($className);
        }
        return $this->cache[$className];
    }
}