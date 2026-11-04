<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Prods\LegalStatus;

/**
 * The arguments of `update_prod`: the production and the fields to change.
 */
final readonly class UpdateProdArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "id": {"type": "integer", "description": "the production"},
                "title": {"type": ["string", "null"], "description": "the title"},
                "altTitle": {"type": ["string", "null"], "description": "another title it is known by"},
                "year": {"type": ["integer", "null"], "description": "the year it came out; 0 clears it"},
                "description": {"type": ["string", "null"], "description": "plain text"},
                "instructions": {"type": ["string", "null"], "description": "how to play or use it, plain text"},
                "legalStatus": {"type": ["string", "null"], "description": "from list_options", "enum": ["insales", "forbidden", "unknown", "mia", "unreleased", "recovered", "forbiddenzxart", "allowedzxart", "allowed", "donationware", null]},
                "externalLink": {"type": ["string", "null"], "description": "its own page elsewhere"},
                "youtubeId": {"type": ["string", "null"], "description": "the id of a video on YouTube"},
                "partyId": {"type": ["integer", "null"], "description": "from find_parties, the party it competed at; 0 clears it"},
                "partyPlace": {"type": ["integer", "null"], "description": "its place at the party; 0 clears it"},
                "compo": {"type": ["string", "null"], "description": "the competition at the party, from list_options"},
                "languages": {"type": ["array", "null"], "items": {"type": "string"}, "description": "language codes from list_options; the list replaces the stored one"},
                "hardware": {"type": ["array", "null"], "items": {"type": "string"}, "description": "hardware codes from list_hardware; the list replaces the stored one"},
                "categoryIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "from list_categories; the list replaces the stored one"},
                "groupIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "the groups or group aliases that made it; the list replaces the stored one"},
                "publisherIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "the groups, group aliases, authors or author aliases that published it; the list replaces the stored one"},
                "tags": {"type": ["array", "null"], "items": {"type": "string"}, "description": "tag titles, from find_tags; the list replaces the stored one"},
                "compilationItemIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "for a compilation, the productions and releases it includes; the list replaces the stored one"},
                "seriesProdIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "for a series, the productions in it; the list replaces the stored one"}
            },
            "required": ["id"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<string>|null $languages
     * @param list<string>|null $hardware
     * @param list<int>|null $categoryIds
     * @param list<int>|null $groupIds
     * @param list<int>|null $publisherIds
     * @param list<string>|null $tags
     * @param list<int>|null $compilationItemIds
     * @param list<int>|null $seriesProdIds
     */
    public function __construct(
        public int $id,
        public ?string $title = null,
        public ?string $altTitle = null,
        public ?int $year = null,
        public ?string $description = null,
        public ?string $instructions = null,
        public ?LegalStatus $legalStatus = null,
        public ?string $externalLink = null,
        public ?string $youtubeId = null,
        public ?int $partyId = null,
        public ?int $partyPlace = null,
        public ?string $compo = null,
        public ?array $languages = null,
        public ?array $hardware = null,
        public ?array $categoryIds = null,
        public ?array $groupIds = null,
        public ?array $publisherIds = null,
        public ?array $tags = null,
        public ?array $compilationItemIds = null,
        public ?array $seriesProdIds = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            id: $arguments->id('id'),
            title: $arguments->text('title'),
            altTitle: $arguments->text('altTitle'),
            year: $arguments->number('year'),
            description: $arguments->text('description'),
            instructions: $arguments->text('instructions'),
            legalStatus: LegalStatus::tryFrom($arguments->oneOf('legalStatus', array_column(LegalStatus::cases(), 'value')) ?? ''),
            externalLink: $arguments->text('externalLink'),
            youtubeId: $arguments->text('youtubeId'),
            partyId: $arguments->number('partyId'),
            partyPlace: $arguments->number('partyPlace'),
            compo: $arguments->text('compo'),
            languages: $arguments->texts('languages'),
            hardware: $arguments->texts('hardware'),
            categoryIds: $arguments->ids('categoryIds'),
            groupIds: $arguments->ids('groupIds'),
            publisherIds: $arguments->ids('publisherIds'),
            tags: $arguments->texts('tags'),
            compilationItemIds: $arguments->ids('compilationItemIds'),
            seriesProdIds: $arguments->ids('seriesProdIds'),
        );
    }
}
