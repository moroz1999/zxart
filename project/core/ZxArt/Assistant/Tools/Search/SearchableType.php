<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use ZxArt\Shared\EntityType;

/**
 * The entity types the `search` tool covers.
 */
enum SearchableType: string
{
    case Prod = 'prod';
    case Release = 'release';
    case Author = 'author';
    case AuthorAlias = 'authorAlias';
    case Group = 'group';
    case GroupAlias = 'groupAlias';

    public function entityType(): EntityType
    {
        return match ($this) {
            self::Prod => EntityType::Prod,
            self::Release => EntityType::Release,
            self::Author => EntityType::Author,
            self::AuthorAlias => EntityType::AuthorAlias,
            self::Group => EntityType::Group,
            self::GroupAlias => EntityType::GroupAlias,
        };
    }
}
