<?php

namespace Quiz\ORM\Scheme\Attribute;

abstract class Relation extends Key
{
    public function __construct(
        protected string $class,
        protected string $localKey,
        protected string $relationKey,
    )
    {
        if (!class_exists($class)){
            throw new \InvalidArgumentException('Relation requires an existing class.');
        }
        parent::__construct();
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    public function getRelationKey(): string
    {
        return $this->relationKey;
    }

}