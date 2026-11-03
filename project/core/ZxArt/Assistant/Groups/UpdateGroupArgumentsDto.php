<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Groups\GroupTypes;

/**
 * The arguments of `update_group`: the group and the fields to change.
 */
final readonly class UpdateGroupArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "id": {"type": "integer", "description": "the group"},
                "title": {"type": "string", "description": "the name"},
                "abbreviation": {"type": "string", "description": "the short name"},
                "type": {"type": "string", "description": "the kind of group, from list_options", "enum": ["unknown", "company", "crack", "studio", "scene", "education", "store", "science"]},
                "countryId": {"type": "integer", "description": "from find_locations; 0 clears it"},
                "cityId": {"type": "integer", "description": "from find_locations; 0 clears it"},
                "website": {"type": "string", "description": "its own site"},
                "wikiLink": {"type": "string", "description": "the page on SpeccyWiki"},
                "slogan": {"type": "string", "description": "its motto"},
                "startDate": {"type": "integer", "description": "when it was founded, a Unix timestamp; 0 clears it"},
                "endDate": {"type": "integer", "description": "when it ended, a Unix timestamp; 0 clears it"},
                "subGroupIds": {"type": "array", "items": {"type": "integer"}, "description": "the groups that belong to this one; the list replaces the stored one"}
            },
            "required": ["id"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<int>|null $subGroupIds
     */
    public function __construct(
        public int $id,
        public ?string $title = null,
        public ?string $abbreviation = null,
        public ?string $type = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?string $website = null,
        public ?string $wikiLink = null,
        public ?string $slogan = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
        public ?array $subGroupIds = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            id: $arguments->id('id'),
            title: $arguments->text('title'),
            abbreviation: $arguments->text('abbreviation'),
            type: $arguments->oneOf('type', GroupTypes::LIST),
            countryId: $arguments->number('countryId'),
            cityId: $arguments->number('cityId'),
            website: $arguments->text('website'),
            wikiLink: $arguments->text('wikiLink'),
            slogan: $arguments->text('slogan'),
            startDate: $arguments->number('startDate'),
            endDate: $arguments->number('endDate'),
            subGroupIds: $arguments->ids('subGroupIds'),
        );
    }
}
