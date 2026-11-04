<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Releases\ReleaseTypes;

/**
 * The arguments of `create_release`.
 */
final readonly class CreateReleaseArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "title": {"type": "string", "description": "the title"},
                "year": {"type": ["integer", "null"], "description": "the year it came out; 0 clears it"},
                "version": {"type": ["string", "null"], "description": "its version"},
                "description": {"type": ["string", "null"], "description": "plain text"},
                "releaseType": {"type": ["string", "null"], "description": "from list_options", "enum": ["unknown", "original", "rerelease", "adaptation", "localization", "bugfix", "mod", "crack", "mia", "corrupted", "compilation", "incomplete", "demoversion", null]},
                "releaseFormats": {"type": ["array", "null"], "items": {"type": "string"}, "description": "release format codes from list_options; the list replaces the stored one"},
                "languages": {"type": ["array", "null"], "items": {"type": "string"}, "description": "language codes from list_options; the list replaces the stored one"},
                "hardware": {"type": ["array", "null"], "items": {"type": "string"}, "description": "hardware codes from list_hardware the release needs beyond its production; the list replaces the stored one"},
                "publisherIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "the groups, group aliases, authors or author aliases that published it; the list replaces the stored one"},
                "prodId": {"type": "integer", "description": "the production it is a release of; another one moves it"}
            },
            "required": ["title", "prodId"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<string>|null $releaseFormats
     * @param list<string>|null $languages
     * @param list<string>|null $hardware
     * @param list<int>|null $publisherIds
     */
    public function __construct(
        public string $title,
        public int $prodId,
        public ?int $year = null,
        public ?string $version = null,
        public ?string $description = null,
        public ?ReleaseTypes $releaseType = null,
        public ?array $releaseFormats = null,
        public ?array $languages = null,
        public ?array $hardware = null,
        public ?array $publisherIds = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            title: $arguments->requiredText('title'),
            year: $arguments->number('year'),
            version: $arguments->text('version'),
            description: $arguments->text('description'),
            releaseType: ReleaseTypes::tryFrom($arguments->oneOf('releaseType', ReleaseTypes::getAllValues()) ?? ''),
            releaseFormats: $arguments->texts('releaseFormats'),
            languages: $arguments->texts('languages'),
            hardware: $arguments->texts('hardware'),
            publisherIds: $arguments->ids('publisherIds'),
            prodId: $arguments->id('prodId'),
        );
    }
}
