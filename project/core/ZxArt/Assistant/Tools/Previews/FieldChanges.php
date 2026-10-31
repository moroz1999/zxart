<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Previews;

use BackedEnum;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * The rows a preview shows for a fields DTO applied over the current one: a row
 * for every field the patch gives and that differs. A field named `…Id` or
 * `…Ids` holds ids, shown as mentions of what they name; an id of nothing
 * stays a number, an id of 0 is no value.
 *
 * @psalm-api
 */
final readonly class FieldChanges
{
    public function __construct(
        private Mentions $mentions,
    ) {
    }

    /**
     * @template T of object
     * @param T|null $before the current fields, every one set; null for a new entity
     * @param T $patch the fields to change; a null field stays as it is
     * @return list<FieldChangeDto>
     */
    public function diff(?object $before, object $patch): array
    {
        $current = $before !== null ? get_object_vars($before) : [];
        $changes = [];
        /** @var mixed $after */
        foreach (get_object_vars($patch) as $field => $after) {
            if ($after === null) {
                continue;
            }
            $isId = str_ends_with($field, 'Id') || str_ends_with($field, 'Ids');
            $beforeValue = $this->value($current[$field] ?? null, $isId);
            $afterValue = $this->value($after, $isId);
            // mentions are equal by value, not by identity
            if (serialize($beforeValue) !== serialize($afterValue)) {
                $changes[] = new FieldChangeDto($field, $beforeValue, $afterValue);
            }
        }

        return $changes;
    }

    /**
     * @return string|int|bool|EntityMentionDto|list<string|int|EntityMentionDto>|null
     */
    private function value(mixed $value, bool $isId): string|int|bool|EntityMentionDto|array|null
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($isId && is_int($value)) {
            return $value === 0 ? null : $this->mentions->one($value);
        }
        if ($isId && is_array($value)) {
            /** @var list<int> $value */
            return $this->mentions->list(array_values($value));
        }
        /** @var string|int|bool|list<string>|null $value */
        return $value;
    }
}
