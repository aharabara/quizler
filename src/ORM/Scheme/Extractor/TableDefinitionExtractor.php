<?php

namespace Quiz\ORM\Scheme\Extractor;

use Quiz\ORM\Scheme\Definition\TableDefinition;
use Symfony\Component\String\Inflector\EnglishInflector;
use function Symfony\Component\String\s;

class TableDefinitionExtractor implements DefinitionExtractorInterface
{
    private EnglishInflector $inflector;
    private ColumnDefinitionExtractor $columnDefinitionExtractor;

    /** @fixme make it stateless */
    public function __construct()
    {
        $this->inflector = new EnglishInflector();
        $this->columnDefinitionExtractor = new ColumnDefinitionExtractor();
    }

    public function extract(string $className): TableDefinition
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class '$className' does not exit.");
        }

        $columns = $this->columnDefinitionExtractor->extract($className);

        $tableName= s($className)->afterLast("\\")->snake()->toString();
        [$tableName] = $this->inflector->pluralize($tableName);

        return new TableDefinition($className, $tableName, ...$columns);
    }
}