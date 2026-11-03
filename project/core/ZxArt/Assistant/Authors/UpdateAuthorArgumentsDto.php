<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `update_author`: the author and the fields to change.
 */
final readonly class UpdateAuthorArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "id": {"type": "integer", "description": "the author"},
                "title": {"type": "string", "description": "the nickname"},
                "realName": {"type": "string", "description": "the real name, only when the source states it"},
                "countryId": {"type": "integer", "description": "from find_locations; 0 clears it"},
                "cityId": {"type": "integer", "description": "from find_locations; 0 clears it"},
                "wikiLink": {"type": "string", "description": "the page on SpeccyWiki"}
            },
            "required": ["id"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $id,
        public ?string $title = null,
        public ?string $realName = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?string $wikiLink = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            id: $arguments->id('id'),
            title: $arguments->text('title'),
            realName: $arguments->text('realName'),
            countryId: $arguments->number('countryId'),
            cityId: $arguments->number('cityId'),
            wikiLink: $arguments->text('wikiLink'),
        );
    }
}
