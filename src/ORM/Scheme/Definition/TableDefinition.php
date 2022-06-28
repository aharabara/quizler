<?php

namespace Quiz\ORM\Scheme\Definition;

use Quiz\Core\Collection;

class TableDefinition
{
    private Collection $columns;

    public function __construct(
        protected string $class,
        protected string $name,
        ColumnDefinition ...$columns,
    ) {
        $this->columns = new Collection($columns);
    }

    /** @return ColumnDefinition[]|Collection */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    public function getUniqueColumn(): ?ColumnDefinition
    {
        return $this->columns->firstWhere(fn (ColumnDefinition $columnDef) => $columnDef->isUniqueField());
    }

    public function getIdentityColumn(): ?ColumnDefinition
    {
        return $this->columns->firstWhere(fn (ColumnDefinition $columnDef) => $columnDef->isIdentityField());
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
        $this->columns = $this->columns->filter();
        $this->columns->uasort(function (ColumnDefinition $colA, ColumnDefinition $colB) {
            return strlen($colA->getName()) <=> strlen($colB->getName());
        });

        return $this->columns
            ->filter(fn (ColumnDefinition $def) => !$def->isCollectionField())
            ->map(fn ($col) => "    $col")
            ->sprintf("CREATE TABLE IF NOT EXISTS {$this->name} (\n%s\n);", ",\n");
    }
}
