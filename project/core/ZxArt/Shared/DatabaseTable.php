<?php

declare(strict_types=1);

namespace ZxArt\Shared;

enum DatabaseTable: string
{
    case Author = 'module_author';
    case AuthorAlias = 'module_authoralias';
    case Authorship = 'authorship';
    case Comment = 'module_comment';
    case StructureLinks = 'structure_links';
    case VotesHistory = 'votes_history';
    case ZxMusic = 'module_zxmusic';
    case ZxPicture = 'module_zxpicture';
    case ZxProd = 'module_zxprod';
    case ZxRelease = 'module_zxrelease';
}
