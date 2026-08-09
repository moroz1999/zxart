<?php

declare(strict_types=1);

namespace ZxArt\Tests\Hardware;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Hardware\Dto\HardwareItemDto;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Releases\Services\ReleaseHardwareAutofillService;

/**
 * The format → hardware rules.
 *
 * The service only ever proposes additions, so every test asserts the exact set
 * it wants written; "adds nothing" is a result in its own right and is asserted
 * as often as a positive one.
 */
#[AllowMockObjectsWithoutExpectations]
class ReleaseHardwareAutofillServiceTest extends TestCase
{
    private ReleaseHardwareAutofillService $service;

    protected function setUp(): void
    {
        $this->service = new ReleaseHardwareAutofillService($this->createCatalog());
    }

    /**
     * @param string[] $formats
     * @param list<string> $expected
     */
    #[DataProvider('machineIndependentRules')]
    public function testFormatAloneDeterminesMediumAndDos(array $formats, array $expected): void
    {
        $this->assertSame($expected, $this->service->getAdditions($formats, []));
    }

    public static function machineIndependentRules(): iterable
    {
        yield 'tape from tap' => [['tap'], ['tape']];
        yield 'tape from tzx' => [['tzx'], ['tape']];
        yield 'microdrive' => [['mdr'], ['microdrive']];
        // the container names the interface, never the filesystem on it
        yield 'trd is a beta disk container' => [['trd'], ['betadisk']];
        yield 'scl is a beta disk container' => [['scl'], ['betadisk']];
        yield 'fdi is a beta disk container' => [['fdi'], ['betadisk']];
        yield 'udi is a beta disk container' => [['udi'], ['betadisk']];
        yield 'td0 is a beta disk container' => [['td0'], ['betadisk']];
        yield 'cpm' => [['cpm'], ['cpm']];
        yield 'opd' => [['opd'], ['opd']];
        yield 'd40 is MDOS' => [['d40'], ['mdos']];
        yield 'd80 is MDOS' => [['d80'], ['mdos']];
        yield 'mbd is MB-02' => [['mbd'], ['mb02']];
        yield 'mld is Dandanator' => [['mld'], ['dandanator']];
        yield 'dck is a Timex cartridge' => [['dck'], ['timex_cartridge']];
        yield 'spg exists only for TSConf' => [['spg'], ['tsconf']];
        yield 'nex is ZX Next' => [['nex'], ['zxnext']];
        yield 'snx is ZX Next' => [['snx'], ['zxnext']];
        yield 'sad is a Sam disk' => [['sad'], ['samdos']];
        yield 'tar is the esxdos archive' => [['tar'], ['esxdos']];
    }

    /**
     * @param string[] $formats
     */
    #[DataProvider('formatsWithoutRules')]
    public function testFormatsThatSayNothingAddNothing(array $formats): void
    {
        $this->assertSame([], $this->service->getAdditions($formats, []));
    }

    public static function formatsWithoutRules(): iterable
    {
        foreach ([['bin'], ['rom'], ['img'], ['z80'], ['sna'], ['szx'], ['slt'], ['$b'], ['$c']] as $formats) {
            yield $formats[0] => [$formats];
        }
    }

    public function testEmptyFormatListAddsNothing(): void
    {
        $this->assertSame([], $this->service->getAdditions([], ['zx48']));
    }

    public function testDskOnASpectrumIsAPlusThreeDisk(): void
    {
        // the drive, not +3DOS: the +3 shipped with CP/M Plus too
        $this->assertSame(['3dosdisk'], $this->service->getAdditions(['dsk'], ['zx128+3']));
    }

    public function testDskOnASamIsSamdos(): void
    {
        $this->assertSame(['samdos'], $this->service->getAdditions(['dsk'], ['samcoupe']));
    }

    public function testMgtOnASpectrumIsGdosRatherThanAGuessedInterface(): void
    {
        $additions = $this->service->getAdditions(['mgt'], ['zx48']);

        $this->assertSame(['gdos'], $additions);
        $this->assertNotContains('disciple', $additions);
        $this->assertNotContains('plusd', $additions);
    }

    public function testMgtOnASamIsSamdos(): void
    {
        $this->assertSame(['samdos'], $this->service->getAdditions(['mgt'], ['samcoupe']));
    }

    public function testAmbiguousFormatWithNoMachineAddsNothing(): void
    {
        $this->assertSame([], $this->service->getAdditions(['dsk'], []));
    }

    public function testAmbiguousFormatWithTwoMachineFamiliesAddsNothing(): void
    {
        $this->assertSame([], $this->service->getAdditions(['dsk'], ['zx48', 'samcoupe']));
    }

    public function testAmbiguousFormatWithAFamilyThatHasNoRowAddsNothing(): void
    {
        $this->assertSame([], $this->service->getAdditions(['dsk'], ['zxnext']));
    }

    /**
     * A Beta Disk image is a Beta Disk image whatever filesystem it carries —
     * CP/M and iS-DOS both ship in these containers — so the medium is added and
     * no operating system is guessed.
     */
    public function testABetaDiskContainerNeverImpliesAFilesystem(): void
    {
        $this->assertSame(['betadisk'], $this->service->getAdditions(['trd'], ['zx48', 'cpm']));
        $this->assertSame(['betadisk'], $this->service->getAdditions(['scl'], ['zx48', 'isdos']));
        $this->assertSame(['betadisk'], $this->service->getAdditions(['trd'], ['zx48']));
    }

    public function testAnExistingDosBlocksTheStageTwoDosButNotItsMedium(): void
    {
        // the +3 drive is still the medium the file lives on, whatever DOS runs on it
        $this->assertSame(['3dosdisk'], $this->service->getAdditions(['dsk'], ['zx128+3', 'cpm']));
    }

    public function testTwoFormatsProposingDifferentDosesWriteNeither(): void
    {
        // .cpm names CP/M, .opd names the Opus system; together they contradict
        $this->assertSame([], $this->service->getAdditions(['cpm', 'opd'], ['zx48']));
    }

    public function testStorageIsNotBlockedByAnExistingDos(): void
    {
        $this->assertSame(['microdrive'], $this->service->getAdditions(['mdr'], ['isdos']));
    }

    public function testCodesAlreadyPresentAreNotProposedAgain(): void
    {
        $this->assertSame([], $this->service->getAdditions(['tap'], ['zx48', 'tape']));
    }

    public function testRunningTwiceProposesNothingTheSecondTime(): void
    {
        $current = ['zx128+3'];
        $first = $this->service->getAdditions(['dsk', 'tap'], $current);
        $this->assertSame(['3dosdisk', 'tape'], $first);

        $this->assertSame([], $this->service->getAdditions(['dsk', 'tap'], [...$current, ...$first]));
    }

    /**
     * The caller passes the release's **effective** set, and this is why.
     *
     * Observed on release 92686: the prod-hardware migration had moved `tape` up
     * to its production, so the release's own set was empty, and auto-fill —
     * comparing against those own codes — proposed adding `tape` straight back.
     * Every save of such a release would have undone the split a little more.
     */
    public function testACodeTheProductionAlreadyStatesIsNotProposed(): void
    {
        // `tape` reaches the service as part of the effective set, not as the
        // release's own code, and must still count as present
        $this->assertSame([], $this->service->getAdditions(['tzx'], ['zx48', 'tape']));
        $this->assertSame([], $this->service->getAdditions(['trd'], ['zx48', 'betadisk']));
    }

    public function testACodeMissingFromTheCatalogIsNeverProposed(): void
    {
        // `mb02` is deliberately absent from this catalog double
        $service = new ReleaseHardwareAutofillService($this->createCatalog(['mb02']));

        $this->assertSame([], $service->getAdditions(['mbd'], []));
    }

    /**
     * A release rarely names a machine — its production does, and the caller
     * passes the effective set — so this is how the format×machine rules resolve
     * in practice.
     */
    public function testTheProductionsMachineResolvesTheFamily(): void
    {
        // kempston is the release's own, zx128+3 comes from the production
        $this->assertSame(['3dosdisk'], $this->service->getAdditions(['dsk'], ['kempston', 'zx128+3']));
        $this->assertSame(['gdos'], $this->service->getAdditions(['mgt'], ['zx48']));
    }

    public function testTwoMachineFamiliesAddNothing(): void
    {
        $this->assertSame([], $this->service->getAdditions(['dsk'], ['zx48', 'samcoupe']));
    }

    public function testHardwareWithoutAnyMachineAddsNothing(): void
    {
        $this->assertSame([], $this->service->getAdditions(['dsk'], ['ay', 'kempston']));
    }

    /**
     * Knowing the family is not knowing the machine: `spectrum` spans a 48K and a
     * Scorpion alike, so a family rule may only contribute storage or a DOS.
     */
    public function testAFamilyRuleNeverAddsAComputerCode(): void
    {
        $additions = $this->service->getAdditions(['dsk'], ['zx128+3']);

        $this->assertSame(['3dosdisk'], $additions);
        foreach ($additions as $code) {
            $this->assertNotSame(HardwareGroup::COMPUTERS, $this->catalogCategory($code));
        }
    }

    private function catalogCategory(string $code): HardwareGroup
    {
        return self::CATALOG[$code];
    }

    /** code => category, mirroring the seeded catalog. */
    private const array CATALOG = [
        'zx48' => HardwareGroup::COMPUTERS,
        'zx16' => HardwareGroup::COMPUTERS,
        'zx128' => HardwareGroup::COMPUTERS,
        'zx128+3' => HardwareGroup::COMPUTERS,
        'samcoupe' => HardwareGroup::COMPUTERS,
        'zxnext' => HardwareGroup::COMPUTERS,
        'tsconf' => HardwareGroup::COMPUTERS,
        'tape' => HardwareGroup::STORAGE,
        'microdrive' => HardwareGroup::STORAGE,
        'betadisk' => HardwareGroup::STORAGE,
        '3dosdisk' => HardwareGroup::STORAGE,
        'mb02' => HardwareGroup::STORAGE,
        'dandanator' => HardwareGroup::STORAGE,
        'timex_cartridge' => HardwareGroup::STORAGE,
        'trdos' => HardwareGroup::DOS,
        '3dos' => HardwareGroup::DOS,
        'cpm' => HardwareGroup::DOS,
        'opd' => HardwareGroup::DOS,
        'mdos' => HardwareGroup::DOS,
        'samdos' => HardwareGroup::DOS,
        'gdos' => HardwareGroup::DOS,
        'esxdos' => HardwareGroup::DOS,
        'isdos' => HardwareGroup::DOS,
        'ay' => HardwareGroup::SOUND,
        'kempston' => HardwareGroup::CONTROLS,
    ];

    /**
     * @param list<string> $excluded codes to leave out, to model a catalog gap
     */
    private function createCatalog(array $excluded = []): HardwareCatalogService&MockObject
    {
        $items = [];
        $grouped = [];
        $id = 0;
        foreach (self::CATALOG as $code => $category) {
            if (in_array($code, $excluded, true)) {
                continue;
            }
            $id++;
            $items[$code] = new HardwareItemDto($id, $code, $category, $id, []);
            $grouped[$category->value][] = $code;
        }

        $catalog = $this->createMock(HardwareCatalogService::class);
        $catalog->method('getItems')->willReturn($items);
        $catalog->method('getGroupedCodes')->willReturn($grouped);

        return $catalog;
    }
}
