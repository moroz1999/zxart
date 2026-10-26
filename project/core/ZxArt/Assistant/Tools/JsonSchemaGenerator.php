<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools;

use BackedEnum;
use LogicException;
use ReflectionClass;
use ReflectionEnum;
use ReflectionNamedType;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use stdClass;

/**
 * Builds the JSON Schema of a request DTO from its constructor: the property
 * types the request deserializer reads (docblock first, then the native type),
 * the `@param` descriptions, and a property without a default as required. So
 * the schema the model sees and the DTO a call builds cannot drift apart.
 *
 * @psalm-api
 */
final readonly class JsonSchemaGenerator
{
    private const array SCALAR_TYPES = [
        TypeIdentifier::INT->value => 'integer',
        TypeIdentifier::FLOAT->value => 'number',
        TypeIdentifier::STRING->value => 'string',
        TypeIdentifier::BOOL->value => 'boolean',
    ];

    private PropertyInfoExtractor $propertyInfo;

    public function __construct()
    {
        $phpDocExtractor = new PhpDocExtractor();
        $this->propertyInfo = new PropertyInfoExtractor(
            typeExtractors: [$phpDocExtractor, new ReflectionExtractor()],
            descriptionExtractors: [$phpDocExtractor],
        );
    }

    /**
     * @param class-string $className
     * @return array<string, mixed>
     * @throws LogicException for a property whose type has no schema
     */
    public function forClass(string $className): array
    {
        $properties = [];
        $required = [];
        $constructor = (new ReflectionClass($className))->getConstructor();
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            $type = $this->propertyInfo->getType($className, $name);
            if ($type === null) {
                throw new LogicException("$className::\$$name has no type to describe");
            }
            $schema = $this->forType($type, "$className::\$$name");
            $description = $this->propertyInfo->getShortDescription($className, $name);
            if ($description !== null && $description !== '') {
                $schema['description'] = $description;
            }
            $properties[$name] = $schema;
            if (!$parameter->isDefaultValueAvailable()) {
                $required[] = $name;
            }
        }

        return [
            'type' => 'object',
            // an empty list would encode as [], and properties are an object
            'properties' => $properties === [] ? new stdClass() : $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function forType(Type $type, string $propertyName): array
    {
        if ($type instanceof NullableType) {
            return $this->nullable($this->forType($type->getWrappedType(), $propertyName));
        }
        if ($type instanceof CollectionType) {
            return ['type' => 'array', 'items' => $this->forType($type->getCollectionValueType(), $propertyName)];
        }
        // an enum in a docblock collection comes as a plain object type
        if ($type instanceof ObjectType) {
            $className = $type->getClassName();
            return is_subclass_of($className, BackedEnum::class)
                ? $this->forEnum($className, $propertyName)
                : $this->forClass($className);
        }
        if ($type instanceof BuiltinType) {
            $scalarType = self::SCALAR_TYPES[$type->getTypeIdentifier()->value] ?? null;
            if ($scalarType !== null) {
                return ['type' => $scalarType];
            }
        }

        throw new LogicException("$propertyName is of type $type, which has no schema");
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     * @return array<string, mixed>
     */
    private function forEnum(string $enumClass, string $propertyName): array
    {
        $backingType = (new ReflectionEnum($enumClass))->getBackingType();
        $scalarType = $backingType instanceof ReflectionNamedType ? self::SCALAR_TYPES[$backingType->getName()] ?? null : null;
        if ($scalarType === null) {
            throw new LogicException("$propertyName is of enum $enumClass, which has no schema");
        }

        return [
            'type' => $scalarType,
            'enum' => array_map(static fn(BackedEnum $case): int|string => $case->value, $enumClass::cases()),
        ];
    }

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function nullable(array $schema): array
    {
        $schema['type'] = [$schema['type'], 'null'];
        if (isset($schema['enum']) && is_array($schema['enum'])) {
            $schema['enum'][] = null;
        }

        return $schema;
    }
}
