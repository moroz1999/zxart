<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Previews;

use BackedEnum;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * The rows a preview shows for a fields DTO applied over the current one: a row
 * for every field the patch gives and that differs. Fields of entity ids show
 * mentions of what they name (an id of nothing stays a number, 0 is no value);
 * date fields, timestamps, show the day (0 is no value); an enum its value.
 *
 * @psalm-api
 */
final readonly class FieldChanges
{
    private const array ID_FIELDS = [
        ProdFieldsDto::class => ['partyId', 'categoryIds', 'groupIds', 'publisherIds', 'compilationItemIds', 'seriesProdIds'],
        ReleaseFieldsDto::class => ['publisherIds', 'prodId'],
        AuthorFieldsDto::class => ['countryId', 'cityId'],
        AuthorAliasFieldsDto::class => ['authorId'],
        GroupFieldsDto::class => ['countryId', 'cityId', 'subGroupIds'],
        GroupAliasFieldsDto::class => ['groupId'],
    ];
    private const array DATE_FIELDS = [
        AuthorAliasFieldsDto::class => ['startDate', 'endDate'],
        GroupFieldsDto::class => ['startDate', 'endDate'],
        GroupAliasFieldsDto::class => ['startDate', 'endDate'],
    ];

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
        $idFields = self::ID_FIELDS[$patch::class] ?? [];
        $dateFields = self::DATE_FIELDS[$patch::class] ?? [];
        $changes = [];
        /** @var mixed $after */
        foreach (get_object_vars($patch) as $field => $after) {
            if ($after === null) {
                continue;
            }
            $isId = in_array($field, $idFields, true);
            $isDate = in_array($field, $dateFields, true);
            $beforeValue = $this->value($current[$field] ?? null, $isId, $isDate);
            $afterValue = $this->value($after, $isId, $isDate);
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
    private function value(mixed $value, bool $isId, bool $isDate): string|int|bool|EntityMentionDto|array|null
    {
        if ($isDate && is_int($value)) {
            return Dates::day($value);
        }
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
