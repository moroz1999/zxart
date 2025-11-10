<?php
declare(strict_types=1);

namespace ZxArt\Hardware;

/**
 * Encapsulates hardware compatibility rules: mapping concrete hardware items
 * to broader compatibility groups used for matching.
 *
 * The mapping is intentionally narrow to the computer families/groups relevant
 * for compatibility checks and mirrors the legacy logic previously embedded in
 * the service.
 */
final class HardwareCompatibilityRules
{
    /**
     * @var array<string,string>
     * key: HardwareItem value (code), value: group id
     */
    private const array ITEM_TO_GROUP = [
        // ZX48 / ZX128 / cloners — group "zx48"
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

    /**
     * @param HardwareItem[] $hardwareItems
     * @return string[] group ids
     */
    public static function itemsToGroups(array $hardwareItems): array
    {
        $resultGroups = [];
        foreach ($hardwareItems as $hardwareItem) {
            $code = $hardwareItem->value;
            if (isset(self::ITEM_TO_GROUP[$code])) {
                $resultGroups[] = self::ITEM_TO_GROUP[$code];
            }
        }
        return array_values(array_unique($resultGroups));
    }

    /**
     * @param string[] $hardwareCodes
     * @return string[] group ids
     */
    public static function codesToGroups(array $hardwareCodes): array
    {
        $resultGroups = [];
        foreach ($hardwareCodes as $hardwareCode) {
            if (isset(self::ITEM_TO_GROUP[$hardwareCode])) {
                $resultGroups[] = self::ITEM_TO_GROUP[$hardwareCode];
            }
        }
        return array_values(array_unique($resultGroups));
    }
}
