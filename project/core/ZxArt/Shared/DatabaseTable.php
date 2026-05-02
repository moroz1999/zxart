<?php

declare(strict_types=1);

namespace ZxArt\Shared;

enum DatabaseTable: string
{
    case Author = 'module_author';
    case AuthorAlias = 'module_authoralias';
    case StructureLinks = 'structure_links';
    case VotesHistory = 'votes_history';
    case ZxMusic = 'module_zxmusic';
}
