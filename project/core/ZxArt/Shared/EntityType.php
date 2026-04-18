<?php

declare(strict_types=1);

namespace ZxArt\Shared;

enum EntityType: string
{
    case Author = 'author';
    case AuthorAlias = 'authorAlias';
    case Group = 'group';
    case GroupAlias = 'groupAlias';
    case Prod = 'prod';
    case Release = 'release';
    case Picture = 'picture';
    case Tune = 'tune';
    case Comment = 'comment';
    case Category = 'category';
    case Country = 'country';
    case City = 'city';
    case Party = 'party';
}
