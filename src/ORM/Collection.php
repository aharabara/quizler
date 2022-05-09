<?php

namespace Quiz\ORM;

class Collection extends \ArrayObject
{

    public function groupBy(string $key): static
    {

        $data = [];
        foreach ($this as $record) {
            $data[$record[$key]][] = $record;
        }

        return new static($data);
    }

    public function keyBy(string $key): static
    {

        $data = [];
        foreach ($this as $record) {
            $data[$record[$key]] = $record;
        }

        return new static($data);
    }

    public function first(): mixed
    {
        $array = $this->getArrayCopy();
        return reset($array);
    }

    public function pluck(string|int $column, string|int $index = null): Collection
    {
        $array = $this->getArrayCopy();
        return new static(array_column($array, $column, $index));
    }

    public function push(mixed ...$items): static
    {
        foreach ($items as $item) {
            $this[] = $item;
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

}