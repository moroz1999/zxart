<?php

declare(strict_types=1);

namespace ZxArt\Stats;

enum StatsEventAggregation: string
{
    case View = 'view';
    case Play = 'play';
    case Vote = 'vote';
    case AddZxPicture = 'addZxPicture';
    case AddZxMusic = 'addZxMusic';
    case AddZxProd = 'addZxProd';
    case Comment = 'comment';
    case TagAdded = 'tagAdded';

    public function groupColumn(): string
    {
        return match ($this) {
            self::View,
            self::Play => 'elementId',
            self::Vote,
            self::AddZxPicture,
            self::AddZxMusic,
            self::AddZxProd,
            self::Comment,
            self::TagAdded => 'userId',
        };
    }
}
