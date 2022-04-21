<?php

namespace Quiz\Builder\SchemeBuilder;

class ColumnDefinition
{
    public function __construct(
        protected string $name,
        protected string $type,
        protected bool $nullable = true,
        protected mixed $default = null,
        protected ?Key $key = null,
    )
    {
        $this->type = match ($type){
            'int', 'bool' => 'integer',
            'array' => throw new \LogicException('Array type is not supported for column definitions'),
            default => $type
        };
    }

    public function __toString(){
        $default = $this->default;
        if (is_string($default)){
            $default = "'$default'";
        }

        $default = $default ? "DEFAULT {$default}" : "";

        $keyClass = null;
        if ($this->key){
            $keyClass = get_class($this->key);
        }

        $key = match ($keyClass){
            Identificator::class =>
                "CONSTRAINT {$this->name}_pk PRIMARY KEY" .($this->key->isAutoincrement() ? " autoincrement" : ""),
            Unique::class => "CONSTRAINT {$this->name}_uk UNIQUE KEY",
//            Relation::class => "CONSTRAINT {$this->name}_fk FOREIGN KEY",
            default => ""
        };
        $nullability = !$this->nullable ? "NOT NULL" : "";

        $type = strtoupper($this->type);
        return trim("{$this->name} {$type} {$default} {$nullability} {$key}");
    }

}