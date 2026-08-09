<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods;

use ZxArt\Hardware\HardwareCompatibilityRules;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use zxProdElement;

/**
 * Decides whether an incoming production and an existing one describe the same
 * software, as far as hardware can tell.
 *
 * Both sides are compared as **one flat set each**, not release by release.
 * Hardware now lives on the production as well as on its releases, so a
 * release-by-release comparison would miss everything an editor recorded on the
 * production — and after the shared codes were moved off the releases it would
 * see empty releases and reject a production against its own re-import, which
 * makes the importer create duplicates.
 */
final class HardwareCompatibilityService
{
    public function areProdAndDtoCompatible(ProdImportDTO $dto, zxProdElement $prod): bool
    {
        $dtoCodes = $this->collectDtoCodes($dto);
        $prodCodes = $prod->getAggregatedHardwareCodes();

        // Whether hardware was *stated* is a different question from whether it
        // could be classified: a source that names nothing is inconclusive, while
        // one that names something we cannot place is a mismatch.
        // Set to true for vtrdos import, where most hardware is undefined clearly.
        if ($dtoCodes === [] && $prodCodes !== []) {
            return true;
        }

        if ($dtoCodes === [] || $prodCodes === []) {
            return false;
        }

        $dtoGroups = HardwareCompatibilityRules::codesToGroups($dtoCodes);
        $prodGroups = HardwareCompatibilityRules::codesToGroups($prodCodes);

        return array_intersect($dtoGroups, $prodGroups) !== [];
    }

    /**
     * Everything the incoming production says about hardware: its own codes plus
     * those of every release it brings.
     *
     * @return list<string>
     */
    private function collectDtoCodes(ProdImportDTO $dto): array
    {
        $codes = $dto->hardwareRequired ?? [];
        foreach ($dto->releases ?? [] as $release) {
            foreach ($release->hardwareRequired ?? [] as $code) {
                $codes[] = $code;
            }
        }

        return array_values(array_unique($codes));
    }
}
