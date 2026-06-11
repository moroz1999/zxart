<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch;

enum PictureSearchOrder: string
{
    case Asc = 'asc';
    case Desc = 'desc';
    case Rand = 'rand';
}
