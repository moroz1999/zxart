<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods;

use ZxArt\Hardware\HardwareCompatibilityRules;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use zxProdElement;

final class HardwareCompatibilityService
{
    public function areProdAndDtoCompatible(ProdImportDTO $dto, zxProdElement $prod): bool
    {
        $dtoReleases = $dto->releases ?? [];
        $prodReleases = $prod->getReleasesList() ?? [];

        $dtoHasHardware = $this->hasHardware($dtoReleases);
        $prodHasHardware = $this->hasHardware($prodReleases);

        if (!$dtoHasHardware && $prodHasHardware) {
            return false;
        }
        if (!$dtoHasHardware && !$prodHasHardware) {
            return false;
        }
        if ($dtoHasHardware && !$prodHasHardware) {
            return false;
        }

        foreach ($dtoReleases as $dtoRelease) {
            $dtoGroups = HardwareCompatibilityRules::codesToGroups($dtoRelease->hardwareRequired ?? []);
            if ($dtoGroups === []) {
                continue;
            }

            foreach ($prodReleases as $prodRelease) {
                $prodGroups = HardwareCompatibilityRules::codesToGroups($prodRelease->hardwareRequired ?? []);
                if ($prodGroups === []) {
                    continue;
                }

                if (array_intersect($dtoGroups, $prodGroups) !== []) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasHardware(iterable $releases): bool
    {
        foreach ($releases as $release) {
            if (!empty($release->hardwareRequired)) {
                return true;
            }
        }
        return false;
    }
}
