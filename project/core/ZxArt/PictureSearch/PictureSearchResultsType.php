<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch;

enum PictureSearchResultsType: string
{
    case Items = 'zxitem';
    case Authors = 'author';
}
