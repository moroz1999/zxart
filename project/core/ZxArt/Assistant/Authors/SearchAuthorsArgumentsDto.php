<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\PageArguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Prods\ProdFilterArguments;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Prods\ProdLink;

/**
 * The arguments of `search_authors`: the filter of the authors, flat,
 * beside the page.
 */
final readonly class SearchAuthorsArgumentsDto
{
    private const string PROPERTIES = <<<'JSON'
        "name": {"type": ["string", "null"], "description": "a part of the nickname, of the real name in any language or of an alias"},
        "countryIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "countries from find_locations"},
        "cityIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "cities from find_locations"},
        "groupIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "a member of the group or one of its aliases"},
        "activeFrom": {"type": ["integer", "null"], "description": "linked to a production of this year or later"},
        "activeTo": {"type": ["integer", "null"], "description": "linked to a production of this year or earlier"},
        "prodIds": {"type": ["array", "null"], "items": {"type": "integer"}, "description": "linked to one of these productions, as prodLink narrows it"},
        "prodRoles": {"type": ["array", "null"], "items": {"type": "string", "enum": ["unknown", "code", "release", "adaptation", "restoring", "music", "sfx", "support", "testing", "graphics", "loading_screen", "intro_code", "intro_graphics", "intro_music", "organizing", "direction", "3dmodels", "design", "logo", "font", "ascii", "illustrating", "tools", "localization", "concept", "gamedesign", "leveldesign", "story", "text", "editing", "translation", "video", "guest"]}, "description": "the roles of the credit on the productions given or found, or on any when none are; roles leave out the publishers, who hold none"},
        "prodLink": {"type": ["string", "null"], "enum": ["made", "published", null], "description": "how the author is linked to the productions given or found; alone, authors linked so to any"},
        "coAuthorId": {"type": ["integer", "null"], "description": "credited on a production together with this author"}
        JSON;

    public const string SCHEMA = '{"type": "object", "properties": {' . self::PROPERTIES . ', "prods": '
        . ProdFilterArguments::SCHEMA . ', ' . PageArguments::PROPERTIES . '}, "additionalProperties": false}';

    public function __construct(
        public AuthorSearchFilterDto $filter,
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
    private static function filter(Arguments $arguments): AuthorSearchFilterDto
    {
        return new AuthorSearchFilterDto(
            name: $arguments->text('name'),
            countryIds: $arguments->ids('countryIds') ?? [],
            cityIds: $arguments->ids('cityIds') ?? [],
            groupIds: $arguments->ids('groupIds') ?? [],
            activeFrom: $arguments->number('activeFrom'),
            activeTo: $arguments->number('activeTo'),
            prodIds: $arguments->ids('prodIds') ?? [],
            prodRoles: $arguments->textsOf('prodRoles', AuthorshipRoles::LIST) ?? [],
            prodLink: ProdLink::tryFrom($arguments->oneOf('prodLink', array_column(ProdLink::cases(), 'value')) ?? ''),
            prods: ProdFilterArguments::nested($arguments),
            coAuthorId: $arguments->number('coAuthorId'),
        );
    }
}
