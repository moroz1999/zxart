<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Prods\Repositories\ProdHardwareRepository;

/**
 * Resolves hardware across the prod/release boundary.
 *
 * Two directions, and they are not the same question — keep them apart:
 *
 *  - **aggregated** (production, looking *down*): its own codes plus every
 *    release's. What a catalogue card, the search index or a stats chart should
 *    show, because a production is described by everything it ships.
 *  - **effective** (release, looking *up*): its own codes plus its production's.
 *    What the emulator, the playable-file filter and the list-image preset must
 *    use, because a release runs on the machine its production names even when
 *    it does not repeat it.
 *
 * Results are memoized per element for the request: several callers ask the same
 * question while rendering one page.
 */
final class ProdHardwareService
{
    /** @var array<int, list<string>> */
    private array $aggregatedByProd = [];

    /** @var array<int, list<string>> */
    private array $prodCodesByRelease = [];

    public function __construct(
        private readonly ProdHardwareRepository $repository,
        private readonly HardwareCatalogService $catalogService,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getAggregatedCodes(int $prodId): array
    {
        if ($prodId <= 0) {
            return [];
        }

        return $this->aggregatedByProd[$prodId] ??= $this->repository->getAggregatedCodes($prodId);
    }

    /**
     * The codes a release inherits from its production.
     *
     * @return list<string>
     */
    public function getInheritedCodes(int $releaseId): array
    {
        if ($releaseId <= 0) {
            return [];
        }

        return $this->prodCodesByRelease[$releaseId] ??= $this->repository->getProdCodesForRelease($releaseId);
    }

    /**
     * @param string[] $ownCodes the release's own hardware
     * @return list<string>
     */
    public function getEffectiveCodes(int $releaseId, array $ownCodes): array
    {
        return array_values(array_unique([
            ...$ownCodes,
            ...$this->getInheritedApplicable($releaseId, $ownCodes),
        ]));
    }

    /**
     * The production's codes a release actually takes on: those in categories it
     * says nothing about.
     *
     * Inheritance fills gaps, it never widens a statement. A release that lists
     * machines has listed all of them — it was built for those and no others — so
     * a production saying `zx48, zx128` must not turn a 128K-only release into
     * one claiming both. The same holds category by category: a release naming
     * `3dos` is not also on TR-DOS because its production is, and one naming
     * `beeper` did not grow an AY.
     *
     * Where the release is silent the production's list is the best knowledge
     * there is, and that is the whole value of the link: it is what lets a
     * release carrying no hardware of its own still resolve a machine.
     *
     * Right after the migration this can only ever add codes, because the
     * production holds the intersection of its releases and is therefore a subset
     * of each. It earns its keep later, when a production is edited by hand or a
     * narrower release is added and that no longer holds.
     *
     * @param string[] $ownCodes
     * @return list<string>
     */
    public function getInheritedApplicable(int $releaseId, array $ownCodes): array
    {
        $inherited = $this->getInheritedCodes($releaseId);
        if ($inherited === [] || $ownCodes === []) {
            return $inherited;
        }

        $spokenFor = [];
        foreach ($ownCodes as $code) {
            $category = $this->catalogService->getCategoryOf($code);
            if ($category !== null) {
                $spokenFor[$category->value] = true;
            }
        }

        $applicable = [];
        foreach ($inherited as $code) {
            $category = $this->catalogService->getCategoryOf($code);
            // a code with no category cannot be matched against the release's
            // statements, so it is inherited rather than silently dropped
            if ($category === null || !isset($spokenFor[$category->value])) {
                $applicable[] = $code;
            }
        }

        return $applicable;
    }

    /**
     * Forgets what was cached for one element, for the long-running jobs that
     * change hardware as they go.
     */
    public function forget(int $elementId): void
    {
        unset($this->aggregatedByProd[$elementId], $this->prodCodesByRelease[$elementId]);
    }
}
