<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use ZxArt\Search\Dto\TypeCountDto;
use ZxArt\Search\Dto\TypeSliceDto;

/**
 * Cuts a page of the paged search out of one list: the results of the types
 * one after another, in the order the types are given.
 *
 * @psalm-api
 */
final readonly class PageWindow
{
    /**
     * @param TypeCountDto[] $counts in the order of the types
     * @return TypeSliceDto[] the types the page takes from, in the same order
     */
    public function slices(int $offset, int $limit, array $counts): array
    {
        $slices = [];
        $start = 0;
        $end = $offset + $limit;
        foreach ($counts as $count) {
            $typeEnd = $start + $count->count;
            $from = max($offset, $start);
            $to = min($end, $typeEnd);
            if ($to > $from) {
                $slices[] = new TypeSliceDto($count->type, $from - $start, $to - $from);
            }
            $start = $typeEnd;
        }

        return $slices;
    }
}
