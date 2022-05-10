<?php

namespace Quiz\Core;

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
        return array_shift($array);
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

    public function firstWhere(callable $callback)
    {
        return $this
            ->filter($callback)
            ->first();
    }

    public function filter(?callable $callback = null): Collection
    {
        return new static(array_filter($this->getArrayCopy(), $callback));
    }

    public function map(callable $callback): Collection
    {
        return new static(array_map($callback, $this->getArrayCopy()));
    }

    public function implode(string $delimiter = ''): string
    {
        return implode($delimiter, $this->getArrayCopy());
    }

    public function sprintf(string $template, ?string $delimiter = null): string
    {
        if ($delimiter) {
            return sprintf($template, implode($delimiter, $this->getArrayCopy()));
        }

        return sprintf($template, ...$this->getArrayCopy());
    }

}