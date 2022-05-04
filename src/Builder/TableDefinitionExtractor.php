<?php

namespace Quiz\Builder;

use Quiz\Builder\SchemeBuilder\TableDefinition;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\Reflector;
use Symfony\Component\String\Inflector\EnglishInflector;
use function Symfony\Component\String\s;

class TableDefinitionExtractor implements DefinitionExtractorInterface
{
    private Reflector $reflector;
    private EnglishInflector $inflector;
    private ColumnDefinitionExtractor $columnDefinitionExtractor;

    /** @fixme make it stateless */
    public function __construct()
    {
        $this->inflector = new EnglishInflector();
        $this->reflector = (new BetterReflection())->reflector();
        $this->columnDefinitionExtractor = new ColumnDefinitionExtractor();
    }

    public function extract(string $className): TableDefinition
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class '$className' does not exit.");
        }

        $columns = $this->columnDefinitionExtractor->extract($className);

        $tableName= s($className)->after("\\")->snake();
        [$tableName] = $this->inflector->pluralize($tableName);

        return new TableDefinition($className, $tableName, ...$columns);
    }
}