<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Previews;

/**
 * Counts in a sentence, as previews name what an action touches.
 */
final class Quantities
{
    /**
     * "3 productions, 1 picture and 2 tunes"; the zero counts left out, null
     * when all are zero.
     *
     * @param list<array{int, string, string}> $counts each a count, its singular and its plural
     */
    public static function phrase(array $counts): ?string
    {
        $parts = [];
        foreach ($counts as [$count, $singular, $plural]) {
            if ($count > 0) {
                $parts[] = $count . ' ' . ($count === 1 ? $singular : $plural);
            }
        }
        if ($parts === []) {
            return null;
        }
        $last = array_pop($parts);

        return $parts === [] ? $last : implode(', ', $parts) . ' and ' . $last;
    }
}
