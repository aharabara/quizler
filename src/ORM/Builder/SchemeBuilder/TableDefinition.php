<?php

namespace Quiz\ORM\Builder\SchemeBuilder;

class TableDefinition
{
    private array $columns;

    public function __construct(
        protected string $class,
        protected string $name,
        ColumnDefinition ...$columns,
    )
    {
        $this->columns = $columns;
    }

    /** @return ColumnDefinition[] */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function build(): string
    {
        $this->columns = array_filter($this->columns);
        usort(
            $this->columns,
            fn(ColumnDefinition $colA, ColumnDefinition $colB) => strlen($colA->getName()) <=> strlen($colB->getName())
        );

        return sprintf(
            "CREATE TABLE IF NOT EXISTS {$this->name} (\n%s\n);",
            implode(",\n", array_map(fn($col) => "    $col", $this->columns))
        );
    }

}