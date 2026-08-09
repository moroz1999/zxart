<?php

declare(strict_types=1);

namespace ZxArt\Hardware;

/**
 * Hardware item grouping, read from the editable catalog.
 *
 * Kept as the entry point the existing call sites already use
 * ({@see \HardwareProvider}, {@see \ZxArt\Stats\Services\StatsService}); the
 * data itself lives in {@see HardwareCatalogService}.
 */
final readonly class HardwareCatalog
{
    public function __construct(
        private HardwareCatalogService $catalogService,
    ) {
    }

    /**
     * @return array<string, list<string>> group value => list of item codes
     */
    public function getGroupedItems(): array
    {
        return $this->catalogService->getGroupedCodes();
    }

    /**
     * @return list<string> item codes belonging to the given group
     */
    public function getGroupItems(HardwareGroup $group): array
    {
        return $this->getGroupedItems()[$group->value] ?? [];
    }

    public function getItemGroup(string $item): ?HardwareGroup
    {
        return $this->catalogService->getCategoryOf($item);
    }
}
