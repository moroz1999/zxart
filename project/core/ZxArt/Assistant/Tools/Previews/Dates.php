<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Previews;

/**
 * Dates stored as timestamps, as previews show them.
 */
final class Dates
{
    /**
     * `Y-m-d`; null for 0, a date not set.
     */
    public static function day(int $timestamp): ?string
    {
        return $timestamp === 0 ? null : date('Y-m-d', $timestamp);
    }
}
