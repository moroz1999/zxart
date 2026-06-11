<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch;

enum PictureSearchSort: string
{
    case Year = 'year';
    case Title = 'title';
    case Place = 'place';
    case Date = 'date';
    case Votes = 'votes';
    case CommentsAmount = 'commentsAmount';
    case Views = 'views';
}
