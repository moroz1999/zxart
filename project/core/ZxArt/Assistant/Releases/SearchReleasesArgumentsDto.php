<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\PageArguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Prods\ProdFilterArguments;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Releases\ReleaseTypes;

/**
 * The arguments of `search_releases`: the filter of the releases, flat,
 * beside the page.
 */
final readonly class SearchReleasesArgumentsDto
{
    private const string PROPERTIES = <<<'JSON'
        "title": {"type": ["string", "null"], "description": "a part of the title"},
        "fileName": {"type": ["string", "null"], "description": "a part of the release file's name"},
        "md5": {"type": ["string", "null"], "description": "of the release file or of any file inside it"},
        "prodIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "releases of these productions"},
        "yearFrom": {"type": ["integer", "null"], "description": "of this year or later"},
        "yearTo": {"type": ["integer", "null"], "description": "of this year or earlier"},
        "releaseTypes": {"type": ["array", "null"], "items": {"type": "string", "enum": ["unknown", "original", "rerelease", "adaptation", "localization", "bugfix", "mod", "crack", "mia", "corrupted", "compilation", "incomplete", "demoversion"]}},
        "formats": {"type": ["array", "null"], "items": {"type": "string"}, "description": "release format codes from list_options"},
        "hardware": {"type": ["array", "null"], "items": {"type": "string"}, "description": "hardware codes from list_hardware the release requires"},
        "languages": {"type": ["array", "null"], "items": {"type": "string"}, "description": "language codes from list_options of the release"},
        "publisherIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "authors or groups that published the release"},
        "authorIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "authors or author aliases credited on the release itself, all of them unless anyAuthor"},
        "anyAuthor": {"type": ["boolean", "null"], "description": "any of authorIds is enough"},
        "authorRoles": {"type": ["array", "null"], "items": {"type": "string", "enum": ["unknown", "code", "release", "adaptation", "restoring", "music", "sfx", "support", "testing", "graphics", "loading_screen", "intro_code", "intro_graphics", "intro_music", "organizing", "direction", "3dmodels", "design", "logo", "font", "ascii", "illustrating", "tools", "localization", "concept", "gamedesign", "leveldesign", "story", "text", "editing", "translation", "video", "guest"]}, "description": "the roles the authors, or anyone when no author is given, are credited with"}
        JSON;

    public const string SCHEMA = '{"type": "object", "properties": {' . self::PROPERTIES . ', "prods": '
        . ProdFilterArguments::SCHEMA . ', ' . PageArguments::PROPERTIES . '}, "additionalProperties": false}';

    public function __construct(
        public ReleaseSearchFilterDto $filter,
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
    private static function filter(Arguments $arguments): ReleaseSearchFilterDto
    {
        return new ReleaseSearchFilterDto(
            title: $arguments->text('title'),
            fileName: $arguments->text('fileName'),
            md5: $arguments->text('md5'),
            prodIds: $arguments->ids('prodIds') ?? [],
            prods: ProdFilterArguments::nested($arguments),
            yearFrom: $arguments->number('yearFrom'),
            yearTo: $arguments->number('yearTo'),
            releaseTypes: array_map(
                static fn(string $type): ReleaseTypes => ReleaseTypes::from($type),
                $arguments->textsOf('releaseTypes', ReleaseTypes::getAllValues()) ?? [],
            ),
            formats: $arguments->texts('formats') ?? [],
            hardware: $arguments->texts('hardware') ?? [],
            languages: $arguments->texts('languages') ?? [],
            publisherIds: $arguments->ids('publisherIds') ?? [],
            authorIds: $arguments->ids('authorIds') ?? [],
            anyAuthor: $arguments->flag('anyAuthor'),
            authorRoles: $arguments->textsOf('authorRoles', AuthorshipRoles::LIST) ?? [],
        );
    }
}
