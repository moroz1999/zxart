<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\ProdLink;
use ZxArt\Releases\ReleaseTypes;

/**
 * The production filter the assistant searches by: `search_prods` takes its
 * fields beside the page, and the other searches nest it as `prods`. Every
 * field given must hold; the dates of adding are not offered.
 */
final class ProdFilterArguments
{
    public const string PROPERTIES = <<<'JSON'
        "title": {"type": "string", "description": "a part of the title"},
        "categoryIds": {"type": "array", "items": {"type": "integer"}, "description": "categories from list_categories; a category takes in its subcategories"},
        "yearFrom": {"type": "integer", "description": "of this year of the production or later"},
        "yearTo": {"type": "integer", "description": "of this year of the production or earlier"},
        "releaseYearFrom": {"type": "integer", "description": "with a release of this year or later"},
        "releaseYearTo": {"type": "integer", "description": "with a release of this year or earlier"},
        "hardware": {"type": "array", "items": {"type": "string"}, "description": "hardware codes from list_hardware, on the production or a release"},
        "languages": {"type": "array", "items": {"type": "string"}, "description": "language codes from list_options, on the production or a release"},
        "legalStatuses": {"type": "array", "items": {"type": "string", "enum": ["insales", "forbidden", "unknown", "mia", "unreleased", "recovered", "forbiddenzxart", "allowedzxart", "allowed", "donationware"]}},
        "releaseTypes": {"type": "array", "items": {"type": "string", "enum": ["unknown", "original", "rerelease", "adaptation", "localization", "bugfix", "mod", "crack", "mia", "corrupted", "compilation", "incomplete", "demoversion"]}, "description": "of any release"},
        "formats": {"type": "array", "items": {"type": "string"}, "description": "release format codes from list_options, of any release"},
        "authorIds": {"type": "array", "items": {"type": "integer"}, "description": "authors or author aliases credited on the production itself, all of them unless anyAuthor"},
        "anyAuthor": {"type": "boolean", "description": "any of authorIds is enough"},
        "authorRoles": {"type": "array", "items": {"type": "string", "enum": ["unknown", "code", "release", "adaptation", "restoring", "music", "sfx", "support", "testing", "graphics", "loading_screen", "intro_code", "intro_graphics", "intro_music", "organizing", "direction", "3dmodels", "design", "logo", "font", "ascii", "illustrating", "tools", "localization", "concept", "gamedesign", "leveldesign", "story", "text", "editing", "translation", "video", "guest"]}, "description": "the roles the authors, or anyone when no author is given, are credited with"},
        "groupIds": {"type": "array", "items": {"type": "integer"}, "description": "groups or group aliases that made or published the production, as groupLink narrows it"},
        "groupLink": {"type": "string", "enum": ["made", "published"], "description": "what groupIds did for the production"},
        "publisherIds": {"type": "array", "items": {"type": "integer"}, "description": "authors or groups that published the production"},
        "countryIds": {"type": "array", "items": {"type": "integer"}, "description": "countries from find_locations of an author or a developer group of the production"},
        "cityIds": {"type": "array", "items": {"type": "integer"}, "description": "cities from find_locations of an author or a developer group of the production"},
        "partyIds": {"type": "array", "items": {"type": "integer"}, "description": "parties from find_parties where the production was released"},
        "compo": {"type": "string", "description": "the compo it took part in"},
        "maxPartyPlace": {"type": "integer", "description": "placed this or higher at its party"},
        "seriesId": {"type": "integer", "description": "the series production whose parts are searched"},
        "compilationId": {"type": "integer", "description": "the compilation whose items are searched"},
        "includedProdId": {"type": "integer", "description": "a production the searched compilations include"},
        "tagIds": {"type": "array", "items": {"type": "integer"}, "description": "tags from find_tags"}
        JSON;

    public const string SCHEMA = '{"type": "object", "description": "the productions to search by", "properties": {'
        . self::PROPERTIES . '}, "additionalProperties": false}';

    /**
     * @throws ToolArgumentsException
     */
    public static function filter(Arguments $arguments): ProdSearchFilterDto
    {
        return new ProdSearchFilterDto(
            title: $arguments->text('title'),
            categoryIds: $arguments->ids('categoryIds') ?? [],
            yearFrom: $arguments->number('yearFrom'),
            yearTo: $arguments->number('yearTo'),
            releaseYearFrom: $arguments->number('releaseYearFrom'),
            releaseYearTo: $arguments->number('releaseYearTo'),
            hardware: $arguments->texts('hardware') ?? [],
            languages: $arguments->texts('languages') ?? [],
            legalStatuses: array_map(
                static fn(string $status): LegalStatus => LegalStatus::from($status),
                $arguments->textsOf('legalStatuses', array_column(LegalStatus::cases(), 'value')) ?? [],
            ),
            releaseTypes: array_map(
                static fn(string $type): ReleaseTypes => ReleaseTypes::from($type),
                $arguments->textsOf('releaseTypes', ReleaseTypes::getAllValues()) ?? [],
            ),
            formats: $arguments->texts('formats') ?? [],
            authorIds: $arguments->ids('authorIds') ?? [],
            anyAuthor: $arguments->flag('anyAuthor'),
            authorRoles: $arguments->textsOf('authorRoles', AuthorshipRoles::LIST) ?? [],
            groupIds: $arguments->ids('groupIds') ?? [],
            groupLink: ProdLink::tryFrom($arguments->oneOf('groupLink', array_column(ProdLink::cases(), 'value')) ?? ''),
            publisherIds: $arguments->ids('publisherIds') ?? [],
            countryIds: $arguments->ids('countryIds') ?? [],
            cityIds: $arguments->ids('cityIds') ?? [],
            partyIds: $arguments->ids('partyIds') ?? [],
            compo: $arguments->text('compo'),
            maxPartyPlace: $arguments->number('maxPartyPlace'),
            seriesId: $arguments->number('seriesId'),
            compilationId: $arguments->number('compilationId'),
            includedProdId: $arguments->number('includedProdId'),
            tagIds: $arguments->ids('tagIds') ?? [],
        );
    }

    /**
     * The nested production filter of another search, null when not given.
     *
     * @throws ToolArgumentsException
     */
    public static function nested(Arguments $arguments): ?ProdSearchFilterDto
    {
        $prods = $arguments->object('prods', self::SCHEMA);

        return $prods !== null ? self::filter($prods) : null;
    }
}
