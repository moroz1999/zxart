<?php

declare(strict_types=1);

namespace ZxArt\GroupList;

enum GroupSortColumn: string
{
    case TITLE = 'title';
    case ID = 'id';

    public static function fromString(string $value, self $default = self::TITLE): self
    {
        return self::tryFrom($value) ?? $default;
    }
}
