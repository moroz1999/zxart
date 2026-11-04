<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `create_author`.
 */
final readonly class CreateAuthorArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "title": {"type": "string", "description": "the nickname"},
                "realName": {"type": ["string", "null"], "description": "the real name, only when the source states it"},
                "countryId": {"type": ["integer", "null"], "description": "from find_locations; 0 clears it"},
                "cityId": {"type": ["integer", "null"], "description": "from find_locations; 0 clears it"},
                "wikiLink": {"type": ["string", "null"], "description": "the page on SpeccyWiki"}
            },
            "required": ["title"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public string $title,
        public ?string $realName = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?string $wikiLink = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            title: $arguments->requiredText('title'),
            realName: $arguments->text('realName'),
            countryId: $arguments->number('countryId'),
            cityId: $arguments->number('cityId'),
            wikiLink: $arguments->text('wikiLink'),
        );
    }
}
