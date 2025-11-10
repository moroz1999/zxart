<?php
declare(strict_types=1);

namespace Tests\Hardware;

use PHPUnit\Framework\TestCase;
use ZxArt\Hardware\HardwareCompatibilityRules;
use ZxArt\Hardware\HardwareItem;

final class HardwareCompatibilityRulesTest extends TestCase
{
    public function testItemsToGroupsReturnsExpectedGroupsAndDeduplicates(): void
    {
        $hardwareItems = [
            HardwareItem::ZX48,
            HardwareItem::ZX128,
            HardwareItem::PENTAGON128,
            HardwareItem::TIMEX2048,
            HardwareItem::ZX80,
            HardwareItem::SPRINTER,
            HardwareItem::ZXUNO,
            // Add an item that maps to the same group to ensure de-duplication
            HardwareItem::ZX16,
        ];

        $groupIds = HardwareCompatibilityRules::itemsToGroups($hardwareItems);

        // Expect groups: zx48 (for many), zx80, sprinter, zxuno
        sort($groupIds);
        $this->assertSame(['sprinter', 'zx48', 'zx80', 'zxuno'], $groupIds);
    }

    public function testItemsToGroupsIgnoresUnknownItemsFromOtherCategories(): void
    {
        $hardwareItems = [
            // Storage/sound/control items should be ignored by mapping rules
            HardwareItem::TAPE,
            HardwareItem::DIVMMC,
            HardwareItem::AY,
            HardwareItem::KEMPSTON,
        ];

        $groupIds = HardwareCompatibilityRules::itemsToGroups($hardwareItems);

        $this->assertSame([], $groupIds);
    }

    public function testCodesToGroupsReturnsExpectedGroupsAndDeduplicates(): void
    {
        $hardwareCodes = [
            'zx48',
            'zx128',
            'timex2068',
            'pentagon1024',
            'zx81',
            'zx8132',
            'atm',
            'baseconf',
            'tsconf',
            // duplicate groups
            'zx16',
        ];

        $groupIds = HardwareCompatibilityRules::codesToGroups($hardwareCodes);

        sort($groupIds);
        $this->assertSame(['atm', 'tsconf', 'zx48', 'zx81'], $groupIds);
    }

    public function testCodesToGroupsSkipsUnknownCodes(): void
    {
        $hardwareCodes = ['unknown', 'another_unknown'];

        $groupIds = HardwareCompatibilityRules::codesToGroups($hardwareCodes);

        $this->assertSame([], $groupIds);
    }
}
