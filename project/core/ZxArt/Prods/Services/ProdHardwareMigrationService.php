<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Releases\ReleaseTypes;
use zxProdElement;
use zxReleaseElement;

/**
 * Works out how one production's hardware should be split between the production
 * and its releases, without touching anything.
 *
 * Two steps, and the second is what makes the first safe.
 *
 * **The production takes the intersection of its `original` releases** — of every
 * release with hardware when there is no original. The originals are what the
 * production *is*; a crack or an adaptation may need more or less without
 * changing that. Releases with no hardware take no part, since one would empty
 * every intersection.
 *
 * **Every release then drops the categories it states exactly as the production
 * does** — all of them, not just the sources. A category that differs even
 * slightly stays whole, because a release does not inherit a category it says
 * anything about (see {@see ProdHardwareService::getInheritedApplicable()}) and
 * would have no way to get the remainder back.
 *
 * That pairing is the whole design. Sourcing from the originals while
 * subtracting the shared set *as a whole* is what damaged the live data:
 *
 *     originals   A {zx48, zx128, ay}   B {zx48, zx128, ay}   → production takes all three
 *     re-release  C {zx128, ay}         → had zx128 and ay subtracted as "already said",
 *                                         kept nothing, and inherited zx48 as well
 *
 * Per category, C keeps its meaning: its machines `{zx128}` differ from the
 * production's `{zx48, zx128}`, so the category stays with C untouched and is
 * not inherited; only `ay`, which matches exactly, moves up and comes straight
 * back down.
 *
 * A release silent in a category does pick the production's up, and that is
 * inheritance working rather than a loss — it never said otherwise.
 *
 * When the sources share nothing the production is left empty rather than given
 * their union: the union would be a claim about the production that is false of
 * some of its releases, and inheritance would then push it onto the silent ones.
 * Such a production still appears in the catalogue and still matches the hardware
 * filter, both of which read the aggregated set.
 */
final readonly class ProdHardwareMigrationService
{
    public function __construct(
        private HardwareCatalogService $catalogService,
    ) {
    }

    /**
     * @return array{prod: list<string>, releases: array<int, list<string>>}|null
     *         null when there is nothing to do
     */
    public function plan(zxProdElement $prod): ?array
    {
        $releases = $prod->getReleasesList() ?: [];
        if ($releases === []) {
            return null;
        }

        $sources = $this->getSourceReleases($releases);
        if ($sources === []) {
            return null;
        }

        $shared = $this->intersectHardware($sources);
        if ($shared === []) {
            return null;
        }

        $releasePlan = [];
        foreach ($releases as $release) {
            $own = $release->hardwareRequired;
            $remaining = $this->subtract($own, $shared);
            if (count($remaining) !== count($own)) {
                $releasePlan[$release->getPersistedId()] = $remaining;
            }
        }

        return ['prod' => $shared, 'releases' => $releasePlan];
    }

    /**
     * Where the shared set is collected from: the `original` releases when there
     * are any, otherwise every release that carries hardware. Releases with no
     * hardware never take part — one would make every intersection empty.
     *
     * The originals decide because they are what the production *is*; a crack or
     * an adaptation may need more or less without changing that. Narrowing the
     * sources this way is only safe because subtraction is per category and
     * exact — see {@see subtract()}. It was not safe when the whole shared set
     * was subtracted at once, which is what damaged the live data.
     *
     * @param zxReleaseElement[] $releases
     * @return list<zxReleaseElement>
     */
    private function getSourceReleases(array $releases): array
    {
        $withHardware = [];
        $originals = [];
        foreach ($releases as $release) {
            if ($release->hardwareRequired === []) {
                continue;
            }
            $withHardware[] = $release;
            if ($release->releaseType === ReleaseTypes::original->value) {
                $originals[] = $release;
            }
        }

        return $originals !== [] ? $originals : $withHardware;
    }

    /**
     * What is left on a release once the production speaks for it.
     *
     * Subtraction happens **per category, and only when the release's codes in
     * that category are exactly the production's**. A release does not inherit a
     * category it says anything about (see
     * {@see ProdHardwareService::getInheritedApplicable()}), so removing part of
     * one would destroy the remainder: a release supporting `kempston,
     * sinclair2` under a production requiring `kempston` would be left owning
     * `sinclair2`, inherit nothing back, and end up claiming the joystick it
     * merely added while losing the one it needs.
     *
     * When the two sets match, everything the release said is said by the
     * production, and the release inherits the category back untouched.
     *
     * @param string[] $own
     * @param list<string> $prodCodes
     * @return list<string>
     */
    private function subtract(array $own, array $prodCodes): array
    {
        $ownByCategory = $this->byCategory($own);
        $prodByCategory = $this->byCategory($prodCodes);

        $remaining = [];
        foreach ($ownByCategory as $category => $codes) {
            $prodCategoryCodes = $prodByCategory[$category] ?? [];
            sort($codes);
            sort($prodCategoryCodes);
            if ($codes !== $prodCategoryCodes) {
                foreach ($ownByCategory[$category] as $code) {
                    $remaining[] = $code;
                }
            }
        }

        return $remaining;
    }

    /**
     * @param string[] $codes
     * @return array<string, list<string>>
     */
    private function byCategory(array $codes): array
    {
        $grouped = [];
        foreach ($codes as $code) {
            // an uncategorised code can never match a category set, so it is
            // parked under its own key and therefore never subtracted
            $category = $this->catalogService->getCategoryOf($code)?->value ?? '?' . $code;
            $grouped[$category][] = $code;
        }

        return $grouped;
    }

    /**
     * @param list<zxReleaseElement> $sources
     * @return list<string>
     */
    private function intersectHardware(array $sources): array
    {
        $shared = null;
        foreach ($sources as $release) {
            $shared = $shared === null
                ? $release->hardwareRequired
                : array_intersect($shared, $release->hardwareRequired);
            if ($shared === []) {
                return [];
            }
        }

        return array_values($shared ?? []);
    }
}
