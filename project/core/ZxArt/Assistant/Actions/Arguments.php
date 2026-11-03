<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

use JsonException;

/**
 * The arguments the model gave an action, checked against the action's schema:
 * a JSON object of the fields the schema has, every required one given. Each
 * value is read by the type the action expects; a value of another type is
 * refused, naming the field, so the error can go back to the model.
 */
final readonly class Arguments
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(
        private array $values,
    ) {
    }

    /**
     * @param string $schemaJson the action's JSON schema
     * @throws ToolArgumentsException
     * @throws JsonException for a schema that is not JSON
     */
    public static function parse(string $json, string $schemaJson): self
    {
        $schema = self::schema($schemaJson);
        try {
            /** @var array<array-key, mixed>|scalar|null $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ToolArgumentsException('The arguments are not valid JSON: ' . $exception->getMessage(), 0, $exception);
        }
        if (!is_array($decoded) || (array_is_list($decoded) && $decoded !== [])) {
            throw new ToolArgumentsException('The arguments must be a JSON object');
        }

        /** @var array<string, mixed> $decoded */
        return self::checked($decoded, $schema, '');
    }

    /**
     * @param array<string, mixed> $values
     * @param array{type: string, properties: array<string, array<string, mixed>>, required?: list<string>, additionalProperties?: bool} $schema
     * @param string $prefix the path of a nested object, to name its fields
     * @throws ToolArgumentsException
     */
    private static function checked(array $values, array $schema, string $prefix): self
    {
        $named = static fn(string $field): string => $prefix . $field;
        $unknown = array_diff(array_keys($values), array_keys($schema['properties']));
        if ($unknown !== []) {
            throw new ToolArgumentsException('Unknown fields: ' . implode(', ', array_map($named, $unknown)));
        }
        $missing = array_diff($schema['required'] ?? [], array_keys($values));
        if ($missing !== []) {
            throw new ToolArgumentsException('Missing fields: ' . implode(', ', array_map($named, $missing)));
        }

        return new self($values);
    }

    /**
     * An action's JSON schema as the model is offered it.
     *
     * @return array{type: string, properties: array<string, array<string, mixed>>, required?: list<string>, additionalProperties?: bool}
     * @throws JsonException
     */
    public static function schema(string $schema): array
    {
        /** @var array{type: string, properties: array<string, array<string, mixed>>, required?: list<string>, additionalProperties?: bool} */
        return json_decode($schema, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws ToolArgumentsException
     */
    public function id(string $name): int
    {
        return $this->number($name) ?? throw new ToolArgumentsException("Missing fields: $name");
    }

    /**
     * @throws ToolArgumentsException
     */
    public function number(string $name): ?int
    {
        $value = $this->values[$name] ?? null;
        if ($value !== null && !is_int($value)) {
            throw new ToolArgumentsException("The field $name must be an integer");
        }

        return $value;
    }

    /**
     * @throws ToolArgumentsException
     */
    public function text(string $name): ?string
    {
        $value = $this->values[$name] ?? null;
        if ($value !== null && !is_string($value)) {
            throw new ToolArgumentsException("The field $name must be a string");
        }

        return $value;
    }

    /**
     * @throws ToolArgumentsException
     */
    public function requiredText(string $name): string
    {
        return $this->text($name) ?? throw new ToolArgumentsException("Missing fields: $name");
    }

    /**
     * A text that must be one of the values given.
     *
     * @param list<string> $values
     * @throws ToolArgumentsException
     */
    public function oneOf(string $name, array $values): ?string
    {
        $value = $this->text($name);
        if ($value !== null && !in_array($value, $values, true)) {
            throw new ToolArgumentsException("The field $name must be one of: " . implode(', ', $values));
        }

        return $value;
    }

    /**
     * Texts that must each be one of the values given.
     *
     * @param list<string> $values
     * @return list<string>|null
     * @throws ToolArgumentsException
     */
    public function textsOf(string $name, array $values): ?array
    {
        $texts = $this->texts($name);
        if ($texts !== null && array_diff($texts, $values) !== []) {
            throw new ToolArgumentsException("The field $name must hold only: " . implode(', ', $values));
        }

        return $texts;
    }

    /**
     * A nested object, read as arguments of its own schema; its fields are
     * named by their path when refused.
     *
     * @param string $schemaJson the nested object's JSON schema
     * @throws ToolArgumentsException
     * @throws JsonException for a schema that is not JSON
     */
    public function object(string $name, string $schemaJson): ?self
    {
        /** @var mixed $value */
        $value = $this->values[$name] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_array($value) || (array_is_list($value) && $value !== [])) {
            throw new ToolArgumentsException("The field $name must be an object");
        }

        /** @var array<string, mixed> $value */
        return self::checked($value, self::schema($schemaJson), $name . '.');
    }

    /**
     * @return list<int>|null
     * @throws ToolArgumentsException
     */
    public function ids(string $name): ?array
    {
        $value = $this->values[$name] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_array($value) || !array_is_list($value) || array_filter($value, static fn(mixed $item): bool => !is_int($item)) !== []) {
            throw new ToolArgumentsException("The field $name must be a list of integers");
        }

        /** @var list<int> $value */
        return $value;
    }

    /**
     * @return list<string>|null
     * @throws ToolArgumentsException
     */
    public function texts(string $name): ?array
    {
        $value = $this->values[$name] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_array($value) || !array_is_list($value) || array_filter($value, static fn(mixed $item): bool => !is_string($item)) !== []) {
            throw new ToolArgumentsException("The field $name must be a list of strings");
        }

        /** @var list<string> $value */
        return $value;
    }

    /**
     * @throws ToolArgumentsException
     */
    public function flag(string $name): bool
    {
        $value = $this->values[$name] ?? false;
        if (!is_bool($value)) {
            throw new ToolArgumentsException("The field $name must be true or false");
        }

        return $value;
    }
}
