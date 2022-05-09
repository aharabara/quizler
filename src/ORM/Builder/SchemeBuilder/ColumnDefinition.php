<?php

namespace Quiz\ORM\Builder\SchemeBuilder;

class ColumnDefinition
{
    protected array $keys;
    private bool $isSearchable = false;
    private bool $isIdentity = false;
    private bool $isRelation = false;

    public function __construct(
        protected string $name,
        protected string $type,
        protected bool $nullable = true,
        protected mixed $default = null,
        Key ...$keys,
    )
    {
        $this->type = match ($type){
            'int', 'bool' => 'integer',
            default => $type
        };

        $this->keys = $keys;

        foreach ($this->keys as $key){
            if ($key instanceof Searchable){
                $this->isSearchable = true;
            }
            if ($key instanceof Identificator){
                $this->isIdentity = true;
            }
            if ($key instanceof Relation){
                $this->isRelation = true;
            }
        }

        if ($this->isIdentity){
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

    public function isCollectionField(): string
    {
        return $this->type === 'array';
    }

    public function isIdentityField(): bool
    {
        return $this->isIdentity;
    }

    public function isSearchable(): bool
    {
        return $this->isSearchable;
    }

    public function isRelationFiled(): bool
    {
        return $this->isRelation;
    }

    public function getRelationAttribute(): ?Relation
    {
        foreach ($this->keys as $key){
            if ($key instanceof Relation) return $key;
        }
        return null;
    }


}