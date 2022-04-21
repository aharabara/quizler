<?php

namespace Quiz\Builder;

use Quiz\Builder\SchemeBuilder\ColumnDefinition;
use Quiz\Builder\SchemeBuilder\Key;
use Quiz\Builder\SchemeBuilder\Relation;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionUnionType;
use Roave\BetterReflection\Reflector\Reflector;
use Symfony\Component\String\Inflector\EnglishInflector;
use function Symfony\Component\String\s;

class SchemeBuilder
{
    private Reflector $reflector;
    private array $columns;
    private string $className;
    private EnglishInflector $inflector;

    public function __construct()
    {
        $this->inflector = new EnglishInflector();
        $this->reflector = (new BetterReflection())->reflector();
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
            $name = s($property->getName())->snake();

            $type = $property->getType();
            if($this->isScalarProperty($type) && $type->getName() !== 'array'){
                $this->columns[] = new ColumnDefinition(
                    $name,
                    $type->getName(),
                    $type->allowsNull(),
                    $property->getDefaultValue(),
                    $this->getConstraintKey($property)
                );
                continue;
            }
            if ($this->isRelationProperty($property)){
                $this->columns[] = new ColumnDefinition(
                    "{$name}_id",
                    "integer", // @todo use identificator property later
                    $type->allowsNull(),
                    null,
                    $this->getConstraintKey($property)
                );
                continue;
            }
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $subType){
                    if ($this->isType($subType->getName(), \DateTimeInterface::class)){
                        $this->columns[] = new ColumnDefinition(
                            "$name",
                            "integer", // @todo use identificator property later
                            $type->allowsNull(),
                            null,
                            $this->getConstraintKey($property)
                        );
                        break;
                    }
                }
                continue;
            }

        }
        return $this;
    }

    public function build(): string
    {
        $tableName= s($this->className)->after("\\")->snake();
        [$tableName] = $this->inflector->pluralize($tableName);

        $baseTemplate = "CREATE TABLE IF NOT EXISTS {$tableName} (%s);";
        // id integer constraint questions_pk primary key autoincrement
        // $name $type $constraints
        return sprintf($baseTemplate, implode(", ", $this->columns));

    }

    protected function getConstraintKey(ReflectionProperty $property): ?Key
    {
        foreach ($property->getAttributes() as $attribute) {
            if ($this->isType($attribute->getName(), Key::class)){
                return new ($attribute->getName())(...$attribute->getArguments()) ;
            }
        }
        return null;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionUnionType|ReflectionNamedType|\Roave\BetterReflection\Reflection\ReflectionIntersectionType|null $type
     * @return bool
     */
    protected function isScalarProperty(\Roave\BetterReflection\Reflection\ReflectionUnionType|ReflectionNamedType|\Roave\BetterReflection\Reflection\ReflectionIntersectionType|null $type): bool
    {
        return $type instanceof ReflectionNamedType && $type->isBuiltin();
    }

    /**
     * @param mixed $property
     * @return bool
     */
    protected function isRelationProperty(mixed $property): bool
    {
        /** @var \Roave\BetterReflection\Reflection\ReflectionAttribute $relation*/
        $relation = $property->getAttributesByName(Relation::class)[0] ?? null;
        return !empty($relation);
    }

    /**
     * @param string $subType
     * @param string $type
     * @return bool
     */
    protected function isType(string $subType, string $type): bool
    {
        return is_subclass_of($subType, $type) || $subType === $type;
    }

}