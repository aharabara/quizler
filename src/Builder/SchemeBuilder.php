<?php

namespace Quiz\Builder;

use Quiz\Builder\SchemeBuilder\ColumnDefinition;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\Reflector;
use Symfony\Component\String\Inflector\EnglishInflector;
use function Symfony\Component\String\s;

class SchemeBuilder
{
    private Reflector $reflector;
    private array $columns;
    private string $className;
    private EnglishInflector $inflector;
    private ColumnDefinitionExtractor $columnDefinitionExtractor;

    /** @fixme make it stateless */
    public function __construct()
    {
        $this->inflector = new EnglishInflector();
        $this->reflector = (new BetterReflection())->reflector();
        $this->columnDefinitionExtractor = new ColumnDefinitionExtractor();
    }

    public function from(string $className): self
    {
        $this->className = $className;
        if (!class_exists($this->className)) {
            throw new \InvalidArgumentException("Class '$className' does not exit.");
        }
        $reflectionClass = $this->reflector->reflectClass($className);

        $this->columns = [];
        foreach ($reflectionClass->getProperties() as $property){
            $this->columns[] = $this->columnDefinitionExtractor->extract($property);
        }
        return $this;
    }

    public function build(): string
    {
        $tableName= s($this->className)->after("\\")->snake();
        [$tableName] = $this->inflector->pluralize($tableName);

        $this->columns = array_filter($this->columns);
        usort(
            $this->columns,
            fn(ColumnDefinition $colA, ColumnDefinition $colB) => strlen($colA->getName()) <=> strlen($colB->getName())
        );
        return sprintf(
            "CREATE TABLE IF NOT EXISTS {$tableName} (\n%s\n);",
            implode(",\n", array_map(fn($col) => "    $col", $this->columns))
        );

    }
}