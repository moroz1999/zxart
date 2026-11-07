<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use ZxArt\Search\Dto\TypeCountDto;
use ZxArt\Search\Dto\TypeSliceDto;

/**
 * Shares the one page of a single-page search out between the types found:
 * evenly, a type with fewer results than its share leaving the rest to the
 * others, and what does not divide evenly going to the types that come first.
 *
 * @psalm-api
 */
final readonly class SinglePageQuota
{
    /**
     * @param TypeCountDto[] $counts in the order of the types
     * @return TypeSliceDto[] the types found, in the same order
     */
    public function slices(int $limit, array $counts): array
    {
        $found = array_values(array_filter($counts, static fn(TypeCountDto $count): bool => $count->count > 0));
        $quotas = $this->fill($limit, $found);

        $slices = [];
        foreach ($found as $index => $count) {
            if ($quotas[$index] > 0) {
                $slices[] = new TypeSliceDto($count->type, 0, $quotas[$index]);
            }
        }

        return $slices;
    }

    /**
     * Each round the types that fit under an even share take all they have;
     * once none does, the rest is shared out between the others.
     *
     * @param list<TypeCountDto> $found
     * @return array<int, int> quota by the index in `$found`
     */
    private function fill(int $limit, array $found): array
    {
        $quotas = [];
        $pending = array_keys($found);
        $remaining = $limit;
        while ($pending !== []) {
            $share = intdiv($remaining, count($pending));
            $fitting = array_filter($pending, static fn(int $index): bool => $found[$index]->count <= $share);
            if ($fitting === []) {
                break;
            }
            foreach ($fitting as $index) {
                $quotas[$index] = $found[$index]->count;
                $remaining -= $found[$index]->count;
            }
            $pending = array_values(array_diff($pending, $fitting));
        }
        if ($pending !== []) {
            $share = intdiv($remaining, count($pending));
            $extra = $remaining % count($pending);
            foreach ($pending as $position => $index) {
                $quotas[$index] = $share + ($position < $extra ? 1 : 0);
            }
        }

        return $quotas;
    }
}
