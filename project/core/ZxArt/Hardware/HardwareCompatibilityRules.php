<?php
declare(strict_types=1);

namespace ZxArt\Hardware;

/**
 * Maps a concrete machine to the **compatibility family** it belongs to: the set
 * of machines that run each other's software.
 *
 * A family is not a machine. `spectrum` covers the Sinclair models *and* the
 * clones that run their software — Pentagon, Scorpion, Profi, Byte, Timex,
 * Didaktik — because for compatibility purposes they are interchangeable. Family
 * ids are internal: nothing stores or publishes them, they only exist to answer
 * "could these two be the same software?".
 *
 * Deliberately narrow — only the computer families that matter for matching.
 */
final class HardwareCompatibilityRules
{
    /**
     * @var array<string,string>
     * key: HardwareItem value (code), value: family id
     */
    private const array ITEM_TO_GROUP = [
        // Sinclair 16/48/128/+2/+3 and the clones that run their software
        'zx48' => 'spectrum',
        'zx16' => 'spectrum',
        'zx128' => 'spectrum',
        'zx128+2' => 'spectrum',
        'zx128+2b' => 'spectrum',
        'zx128+3' => 'spectrum',
        'timex2048' => 'spectrum',
        'timex2068' => 'spectrum',
        'pentagon128' => 'spectrum',
        'pentagon512' => 'spectrum',
        'pentagon1024' => 'spectrum',
        'pentagon2666' => 'spectrum',
        'profi' => 'spectrum',
        'scorpion' => 'spectrum',
        'scorpion1024' => 'spectrum',
        'byte' => 'spectrum',
        'zxmphoenix' => 'spectrum',
        'tk9x' => 'spectrum',
        'alf' => 'spectrum',
        'didaktik80' => 'spectrum',

        // zx80 family
        'zx80' => 'zx80',

        // zx81 family
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
