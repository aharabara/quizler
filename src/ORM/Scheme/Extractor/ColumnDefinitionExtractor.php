<?php

namespace Quiz\ORM\Scheme\Extractor;

use DateTimeInterface;
use Quiz\ORM\Scheme\Attribute\Key;
use Quiz\ORM\Scheme\Attribute\ParentRelation;
use Quiz\ORM\Scheme\Definition\ColumnDefinition;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionAttribute;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionType;
use Roave\BetterReflection\Reflection\ReflectionUnionType;
use Roave\BetterReflection\Reflector\Reflector;
use function Symfony\Component\String\s;

class ColumnDefinitionExtractor implements DefinitionExtractorInterface
{
    public const SCALARS = [
        'int',
        'null',
        'string',
        'float',
        'bool',
        'double',
    ];

    private Reflector $reflector;

    public function __construct()
    {
        $this->reflector = (new BetterReflection())->reflector();
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return ColumnDefinition|null
     */
    protected function extractColumn(ReflectionProperty $property): ?ColumnDefinition
    {
        $type = $property->getType();

        if ($this->isScalarProperty($type)) {
            return $this->extractScalarFieldDefinition($property);
        }

        if ($this->isParentRelationProperty($property)) {
            return $this->extractRelationFieldDefinition($property);
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $subType) {
                if ($this->isType($subType->getName(), DateTimeInterface::class)) {
                    return $this->extractTimestampFieldDefinition($property);
                }
            }
        }

        return null;
    }

    /** @return ColumnDefinition[] */
    public function extract(string $className): array
    {
        $class = $this->reflector->reflectClass($className);

        $columns = [];

        foreach ($class->getProperties() as $property) {
            $columns[] = $this->extractColumn($property);
        }

        return array_filter($columns);
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return array<int, object>
     */
    protected function getConstraintKeys(ReflectionProperty $property): array
    {
        $keys = [];

        foreach ($property->getAttributes() as $attribute) {
            if ($this->isType($attribute->getName(), Key::class)) {
                $keys[] = new ($attribute->getName())(...$attribute->getArguments());
            }
        }

        return $keys;
    }

    /**
     * @param ReflectionType|null $type
     *
     * @return bool
     */
    protected function isScalarProperty(?ReflectionType $type): bool
    {
        $filteringClause = static fn ($type) => in_array((string)$type, self::SCALARS);

        return ($type instanceof ReflectionNamedType && $type->isBuiltin())
            || (
                $type instanceof ReflectionUnionType
                && count(array_filter($type->getTypes(), $filteringClause)) === count($type->getTypes())
            );
    }

    /**
     * @param mixed $property
     *
     * @return bool
     */
    protected function isParentRelationProperty(mixed $property): bool
    {
        /** @var ReflectionAttribute $relation */
        $relation = $property->getAttributesByName(ParentRelation::class)[0] ?? null;

        return $relation !== null;
    }

    /**
     * @param string $subType
     * @param string $type
     *
     * @return bool
     */
    protected function isType(string $subType, string $type): bool
    {
        return is_subclass_of($subType, $type) || $subType === $type;
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return ColumnDefinition
     */
    protected function extractScalarFieldDefinition(ReflectionProperty $property): ColumnDefinition
    {
        $nullable = $property->getType()->allowsNull();
        $type = $property->getType();
        if ($property->getType() instanceof ReflectionUnionType) {
            $type = $property->getType()->getTypes()[0]; // take first, usually it is what we want
        }

        return new ColumnDefinition(
            s($property->getName())->snake(),
            $type->getName(),
            $nullable,
            $property->getDefaultValue(),
            ...$this->getConstraintKeys($property)
        );
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return ColumnDefinition
     */
    protected function extractRelationFieldDefinition(ReflectionProperty $property): ColumnDefinition
    {
        return new ColumnDefinition(
            s($property->getName())->snake()."_id",
            "integer", // @todo use identificator property later
            $property->getType()->allowsNull(),
            null,
            ...$this->getConstraintKeys($property)
        );
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return ColumnDefinition
     */
    protected function extractTimestampFieldDefinition(ReflectionProperty $property): ColumnDefinition
    {
        return new ColumnDefinition(
            s($property->getName())->snake(),
            "integer", // @todo use identificator property later
            $property->getType()->allowsNull(),
            null,
            ...$this->getConstraintKeys($property)
        );
    }
}
