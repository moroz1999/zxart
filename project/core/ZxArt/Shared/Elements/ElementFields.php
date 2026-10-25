<?php

declare(strict_types=1);

namespace ZxArt\Shared\Elements;

use structureElement;

/**
 * Writes the fields an entity write carries onto its element, and reads them
 * back. A `null` value is a field the write leaves untouched; a flag is stored
 * as the 0/1 a checkbox field holds.
 */
final class ElementFields
{
    /**
     * @param array<string, string|int|bool|array<array-key, mixed>|null> $values element field => value
     */
    public static function assign(structureElement $element, array $values): void
    {
        foreach ($values as $field => $value) {
            if ($value === null) {
                continue;
            }
            $element->$field = is_bool($value) ? (int)$value : $value;
        }
    }

    /**
     * A text field, which the element holds HTML-entity-encoded.
     */
    public static function text(structureElement $element, string $field): string
    {
        return html_entity_decode((string)$element->$field, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * A number the element holds in a text or number column, 0 when not set.
     */
    public static function number(structureElement $element, string $field): int
    {
        return (int)$element->$field;
    }

    /**
     * A checkbox field.
     */
    public static function flag(structureElement $element, string $field): bool
    {
        return (bool)$element->$field;
    }

    /**
     * The ids of the elements a connected-elements field holds.
     *
     * @return int[]
     */
    public static function ids(structureElement $element, string $field): array
    {
        /** @var structureElement[] $connected */
        $connected = (array)$element->$field;

        return array_values(array_map(static fn(structureElement $item): int => $item->getId(), $connected));
    }

    /**
     * A list field such as a set of codes. Read from the stored value: a
     * value-set field keeps showing what it held before it was last assigned.
     *
     * @return string[]
     */
    public static function strings(structureElement $element, string $field): array
    {
        /** @var array<int|string> $values */
        $values = (array)self::stored($element, $field);

        return array_values(array_map('strval', $values));
    }

    /**
     * A date field as the timestamp it stores, 0 when it is not set; the
     * element shows it formatted.
     */
    public static function timestamp(structureElement $element, string $field): int
    {
        return (int)self::stored($element, $field);
    }

    private static function stored(structureElement $element, string $field): mixed
    {
        $chunk = $element->getDataChunk($field);

        return $chunk !== null ? $chunk->getStorageValue() : $element->$field;
    }
}
