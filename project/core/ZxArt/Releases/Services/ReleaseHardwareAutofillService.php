<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareCompatibilityRules;

/**
 * Derives the hardware a release file format implies.
 *
 * Only ever **adds** codes, never removes what an editor chose, so re-saving a
 * release changes nothing and a backfill can be re-run safely.
 *
 * Three stages, each narrower than the last:
 *  1. what the format says on its own (medium and DOS);
 *  2. what it says once the release's machine family is known;
 *  3. for the handful of releases naming no machine at all, the family the
 *     production's other releases agree on — the storage and DOS codes only,
 *     never a machine, since a family holds many.
 *
 * "Add nothing" is always the default: a format used by two machines produces no
 * code unless something else disambiguates it.
 */
final readonly class ReleaseHardwareAutofillService
{
    /**
     * Codes the format alone determines, whatever machine it runs on.
     *
     * A disk-image extension names the **medium**, not the filesystem on it:
     * Beta Disk images ship as .trd, .scl, .fdi and .udi and hold TR-DOS, CP/M or
     * iS-DOS alike (there are CP/M .trd files in the wild). So a container
     * contributes its interface and nothing else; only an extension that names
     * one system — .cpm, .opd, .tar — contributes an operating system.
     *
     * @var array<string, list<string>> format => codes
     */
    private const array FORMAT_RULES = [
        'tap' => ['tape'],
        'tzx' => ['tape'],
        'mdr' => ['microdrive'],
        'trd' => ['betadisk'],
        'scl' => ['betadisk'],
        'fdi' => ['betadisk'],
        'udi' => ['betadisk'],
        'td0' => ['betadisk'],
        'cpm' => ['cpm'],
        'opd' => ['opd'],
        'd40' => ['mdos'],
        'd80' => ['mdos'],
        'mbd' => ['mb02'],
        'mld' => ['dandanator'],
        'dck' => ['timex_cartridge'],
        'spg' => ['tsconf'],
        'nex' => ['zxnext'],
        'snx' => ['zxnext'],
        'sad' => ['samdos'],
        'tar' => ['esxdos'],
    ];

    /**
     * Codes that only follow once the machine family is known. A format missing
     * from a family's row adds nothing for that family — the pairing is the
     * whole point.
     *
     * @var array<string, array<string, list<string>>> format => family => codes
     */
    private const array FORMAT_FAMILY_RULES = [
        'dsk' => [
            // the drive, not the filesystem: the +3 shipped with CP/M Plus and
            // .dsk is the generic +3 container
            'spectrum' => ['3dosdisk'],
            'samcoupe' => ['samdos'],
        ],
        'mgt' => [
            // DISCiPLE and MGT +D are alternative interfaces sharing G+DOS; the
            // format cannot say which, so only the DOS is determinable
            'spectrum' => ['gdos'],
            'samcoupe' => ['samdos'],
        ],
    ];

    /**
     * A release already naming one of these has its operating system decided;
     * adding a second one would be a claim the format cannot support. Applies to
     * every DOS any stage emits, not just `trdos` — a CP/M disk image is still a
     * CP/M disk image whatever container it arrived in.
     *
     * @var list<string>
     */
    private const array DOS_CODES = [
        'cpm', 'isdos', 'tasis', 'nedoos', 'mdos', 'tos', 'bsdos', '3dos',
        'esxdos', 'disciple', 'gdos', 'opd', 'samdos', 'trdos', 'trdos4x',
    ];

    public function __construct(
        private HardwareCatalogService $catalogService,
    ) {
    }

    /**
     * @param string[] $releaseFormats
     * @param string[] $currentHardware the release's **effective** hardware — its
     *        own codes plus its production's. The production is where the machine
     *        normally lives, so passing only the release's own codes would leave
     *        most format×machine rules unresolvable.
     * @return list<string> codes to add, empty when nothing is determinable
     */
    public function getAdditions(array $releaseFormats, array $currentHardware): array
    {
        if ($releaseFormats === []) {
            return [];
        }

        $candidates = $this->collectCandidates($releaseFormats, $currentHardware);

        return $this->filterCandidates($candidates, $currentHardware);
    }

    /**
     * @param string[] $releaseFormats
     * @param string[] $currentHardware
     * @return list<string>
     */
    private function collectCandidates(array $releaseFormats, array $currentHardware): array
    {
        $candidates = [];
        $family = $this->resolveFamily($currentHardware);

        foreach ($releaseFormats as $format) {
            $format = strtolower((string)$format);

            foreach (self::FORMAT_RULES[$format] ?? [] as $code) {
                $candidates[] = $code;
            }

            if ($family === null) {
                continue;
            }

            foreach (self::FORMAT_FAMILY_RULES[$format][$family] ?? [] as $code) {
                $candidates[] = $code;
            }
        }

        return $candidates;
    }

    /**
     * The single machine family the codes agree on, or null when they name none
     * or several — an ambiguous release is left alone.
     *
     * @param string[] $hardware
     */
    private function resolveFamily(array $hardware): ?string
    {
        $families = HardwareCompatibilityRules::codesToGroups($hardware);

        return count($families) === 1 ? $families[0] : null;
    }

    /**
     * @param list<string> $candidates
     * @param string[] $currentHardware
     * @return list<string>
     */
    private function filterCandidates(array $candidates, array $currentHardware): array
    {
        $catalog = $this->catalogService->getItems();
        $wanted = [];
        foreach (array_unique($candidates) as $code) {
            // a code the catalog does not know cannot be stored at all
            if (!in_array($code, $currentHardware, true) && isset($catalog[$code])) {
                $wanted[] = $code;
            }
        }

        $dosAllowed = $this->isDosAdditionAllowed($wanted, $currentHardware);

        return array_values(array_filter(
            $wanted,
            static fn(string $code): bool => $dosAllowed || !in_array($code, self::DOS_CODES, true),
        ));
    }

    /**
     * A release gets a derived operating system only when there is exactly one
     * candidate and it has none yet. Several candidates means the formats
     * disagree — a container holding both a TR-DOS and a CP/M image says nothing
     * about which one the release *is*, so neither is written.
     *
     * @param list<string> $wanted
     * @param string[] $currentHardware
     */
    private function isDosAdditionAllowed(array $wanted, array $currentHardware): bool
    {
        if (array_intersect($currentHardware, self::DOS_CODES) !== []) {
            return false;
        }
        $dosCandidates = array_intersect($wanted, self::DOS_CODES);

        return count($dosCandidates) === 1;
    }
}
