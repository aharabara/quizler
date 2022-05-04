<?php

namespace Quiz\Builder\SchemeBuilder;

class ColumnDefinition
{
    protected array $keys;

    public function __construct(
        protected string $name,
        protected string $type,
        protected bool $nullable = true,
        protected mixed $default = null,
        Key ...$key,
    )
    {
        $this->type = match ($type){
            'int', 'bool' => 'integer',
            'array' => throw new \LogicException('Array type is not supported for column definitions'),
            default => $type
        };

        $this->keys = $key;

        if ($this->isIdentityField()){
            $this->nullable = false;
        }
    }

    public function __toString(){
        $default = $this->default;
        if (is_string($default)){
            $default = "'$default'";
        }

        $default = $default ? "DEFAULT {$default}" : "";

        $constraint = '';
        foreach ($this->keys as $key){
            $constraint = match (get_class($key)){
                Identificator::class =>
                    "CONSTRAINT {$this->name}_pk PRIMARY KEY" .($key->isAutoincrement() ? " autoincrement" : ""),
                Unique::class => "CONSTRAINT {$this->name}_uk UNIQUE KEY",
    //            Relation::class => "CONSTRAINT {$this->name}_fk FOREIGN KEY",
                default => ""
            };
        }

        $nullability = !$this->nullable ? "NOT NULL" : "";

        $type = strtoupper($this->type);
        return trim("{$this->name} {$type} {$default} {$nullability} {$constraint}");
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isIdentityField(): bool
    {
        return !empty(array_filter($this->keys, fn(Key $k) => $k instanceof Identificator));
    }

    public function isSearchable(): bool
    {
        return !empty(array_filter($this->keys, fn(Key $k) => $k instanceof Searchable));
    }


}