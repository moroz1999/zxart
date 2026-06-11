<?php

declare(strict_types=1);

namespace ZxArt\MusicSearch;

enum MusicSearchSort: string
{
    case Year = 'year';
    case Title = 'title';
    case Place = 'place';
    case Date = 'date';
    case Votes = 'votes';
    case CommentsAmount = 'commentsAmount';
    case Plays = 'plays';
}
