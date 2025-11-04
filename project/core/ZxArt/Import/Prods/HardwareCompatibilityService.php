<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods;

use ZxArt\Import\Prods\Dto\ProdImportDTO;
use zxProdElement;

final class HardwareCompatibilityService
{
    /**
     * @var array<string,string>
     * code => group
     */
    private const array CODE_TO_GROUP = [
        // ZX48 / ZX128 / cloners
        'zx48' => 'zx48',
        'zx16' => 'zx48',
        'zx128' => 'zx48',
        'zx128+2' => 'zx48',
        'zx128+2b' => 'zx48',
        'zx128+3' => 'zx48',
        'timex2048' => 'zx48',
        'timex2068' => 'zx48',
        'pentagon128' => 'zx48',
        'pentagon512' => 'zx48',
        'pentagon1024' => 'zx48',
        'pentagon2666' => 'zx48',
        'profi' => 'zx48',
        'scorpion' => 'zx48',
        'scorpion1024' => 'zx48',
        'byte' => 'zx48',
        'zxmphoenix' => 'zx48',
        'tk9x' => 'zx48',
        'alf' => 'zx48',
        'didaktik80' => 'zx48',

        // zx80 family
        'zx80' => 'zx80',

        // zx81 family
        'zx81' => 'zx81',
        'zx811' => 'zx81',
        'zx812' => 'zx81',
        'zx8132' => 'zx81',
        'zx8116' => 'zx81',
        'zx8164' => 'zx81',
        'lambda8300' => 'zx81',

        // sprinter
        'sprinter' => 'sprinter',

        // sinclair ql
        'sinclairql' => 'sinclairql',

        // tsconf
        'tsconf' => 'tsconf',

        // atm/baseconf
        'atm' => 'atm',
        'atm2' => 'atm',
        'baseconf' => 'atm',

        // zxnext
        'zxnext' => 'zxnext',

        // element zx
        'elementzxmb' => 'elementzxmb',

        // zxuno
        'zxuno' => 'zxuno',

        // sam coupe
        'samcoupe' => 'samcoupe',
    ];

    public function areProdAndDtoCompatible(ProdImportDTO $dto, zxProdElement $prod): bool
    {
        $dtoReleases = $dto->releases ?? [];
        $prodReleases = $prod->getReleasesList() ?? [];

        $dtoHas = $this->hasHardware($dtoReleases);
        $prodHas = $this->hasHardware($prodReleases);

        if (!$dtoHas && $prodHas) {
            return false;
        }
        if (!$dtoHas && !$prodHas) {
            return false;
        }
        if ($dtoHas && !$prodHas) {
            return false;
        }

        foreach ($dtoReleases as $dtoRelease) {
            $dtoGroups = $this->codesToGroups($dtoRelease->hardwareRequired ?? []);
            if ($dtoGroups === []) {
                continue;
            }

            foreach ($prodReleases as $prodRelease) {
                $prodGroups = $this->codesToGroups($prodRelease->hardwareRequired ?? []);
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
        foreach ($releases as $r) {
            if (!empty($r->hardwareRequired)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string[] $codes
     * @return string[] group ids
     */
    private function codesToGroups(array $codes): array
    {
        $result = [];

        foreach ($codes as $code) {
            if (isset(self::CODE_TO_GROUP[$code])) {
                $result[] = self::CODE_TO_GROUP[$code];
            }
        }

        return array_values(array_unique($result));
    }
}
