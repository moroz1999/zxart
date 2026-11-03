<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\PageArguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Prods\ProdFilterArguments;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Groups\GroupTypes;
use ZxArt\Prods\ProdLink;

/**
 * The arguments of `search_groups`: the filter of the groups, flat,
 * beside the page.
 */
final readonly class SearchGroupsArgumentsDto
{
    private const string PROPERTIES = <<<'JSON'
        "name": {"type": "string", "description": "a part of the title, of the abbreviation or of an alias"},
        "countryIds": {"type": "array", "items": {"type": "integer"}, "description": "countries from find_locations"},
        "cityIds": {"type": "array", "items": {"type": "integer"}, "description": "cities from find_locations"},
        "types": {"type": "array", "items": {"type": "string", "enum": ["unknown", "company", "crack", "studio", "scene", "education", "store", "science"]}},
        "foundedFrom": {"type": "integer", "description": "founded at this Unix timestamp or later"},
        "foundedTo": {"type": "integer", "description": "founded at this Unix timestamp or earlier"},
        "endedFrom": {"type": "integer", "description": "ended at this Unix timestamp or later"},
        "endedTo": {"type": "integer", "description": "ended at this Unix timestamp or earlier"},
        "activeFrom": {"type": "integer", "description": "made or published a production of this year or later"},
        "activeTo": {"type": "integer", "description": "made or published a production of this year or earlier"},
        "memberIds": {"type": "array", "items": {"type": "integer"}, "description": "authors (or their aliases) recorded as members"},
        "prodIds": {"type": "array", "items": {"type": "integer"}, "description": "made or published one of these productions, as prodLink narrows it"},
        "prodLink": {"type": "string", "enum": ["made", "published"], "description": "what the group did for the productions given or found; alone, groups that did it for any"},
        "parentGroupId": {"type": "integer", "description": "a subgroup of this group"},
        "subGroupId": {"type": "integer", "description": "a group this group is a subgroup of"}
        JSON;

    public const string SCHEMA = '{"type": "object", "properties": {' . self::PROPERTIES . ', "prods": '
        . ProdFilterArguments::SCHEMA . ', ' . PageArguments::PROPERTIES . '}, "additionalProperties": false}';

    public function __construct(
        public GroupSearchFilterDto $filter,
        public PageArguments $page,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            filter: self::filter($arguments),
            page: PageArguments::from($arguments),
        );
    }

    /**
     * @throws ToolArgumentsException
     */
    private static function filter(Arguments $arguments): GroupSearchFilterDto
    {
        return new GroupSearchFilterDto(
            name: $arguments->text('name'),
            countryIds: $arguments->ids('countryIds') ?? [],
            cityIds: $arguments->ids('cityIds') ?? [],
            types: $arguments->textsOf('types', GroupTypes::LIST) ?? [],
            foundedFrom: $arguments->number('foundedFrom'),
            foundedTo: $arguments->number('foundedTo'),
            endedFrom: $arguments->number('endedFrom'),
            endedTo: $arguments->number('endedTo'),
            activeFrom: $arguments->number('activeFrom'),
            activeTo: $arguments->number('activeTo'),
            memberIds: $arguments->ids('memberIds') ?? [],
            prodIds: $arguments->ids('prodIds') ?? [],
            prodLink: ProdLink::tryFrom($arguments->oneOf('prodLink', array_column(ProdLink::cases(), 'value')) ?? ''),
            prods: ProdFilterArguments::nested($arguments),
            parentGroupId: $arguments->number('parentGroupId'),
            subGroupId: $arguments->number('subGroupId'),
        );
    }
}
