<?php

declare(strict_types=1);

namespace ZxArt\AuthorList;

enum AuthorSortColumn: string
{
    case TITLE = 'title';
    case GRAPHICS_RATING = 'graphicsRating';
    case MUSIC_RATING = 'musicRating';
    case ID = 'id';

    public static function fromString(string $value, self $default = self::TITLE): self
    {
        return self::tryFrom($value) ?? $default;
    }
}
