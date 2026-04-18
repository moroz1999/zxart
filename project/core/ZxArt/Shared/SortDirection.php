<?php

declare(strict_types=1);

namespace ZxArt\Shared;

enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';

    public static function fromString(string $value, self $default = self::ASC): self
    {
        return self::tryFrom(strtolower($value)) ?? $default;
    }
}
