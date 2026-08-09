<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Prods\Repositories\ProdHardwareRepository;
use ZxArt\Prods\Services\ProdHardwareService;

/**
 * Resolution across the prod/release boundary.
 *
 * The two directions are easy to confuse, so each is pinned separately: a
 * production looks *down* at its releases, a release looks *up* at its
 * production.
 */
#[AllowMockObjectsWithoutExpectations]
class ProdHardwareServiceTest extends TestCase
{
    private ProdHardwareRepository&MockObject $repository;
    private ProdHardwareService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProdHardwareRepository::class);

        $catalog = $this->createMock(HardwareCatalogService::class);
        $catalog->method('getCategoryOf')->willReturnCallback(
            static fn(string $code): ?HardwareGroup => match (true) {
                in_array($code, ['zx48', 'zx128', 'zx128+3', 'pentagon128', 'samcoupe'], true) => HardwareGroup::COMPUTERS,
                in_array($code, ['ay', 'beeper', 'gs'], true) => HardwareGroup::SOUND,
                in_array($code, ['kempston', 'sinclair2', 'cursor', 'int2_2'], true) => HardwareGroup::CONTROLS,
                in_array($code, ['tape', 'betadisk', '3dosdisk'], true) => HardwareGroup::STORAGE,
                in_array($code, ['trdos', '3dos'], true) => HardwareGroup::DOS,
                default => null,
            },
        );
        $catalog->method('getGroupedCodes')->willReturn([
            HardwareGroup::COMPUTERS->value => ['zx48', 'zx128', 'zx128+3', 'samcoupe', 'pentagon128'],
            HardwareGroup::SOUND->value => ['ay', 'gs'],
            HardwareGroup::CONTROLS->value => ['kempston', 'sinclair2', 'cursor'],
            HardwareGroup::DOS->value => ['trdos', '3dos'],
        ]);

        $this->service = new ProdHardwareService($this->repository, $catalog);
    }

    public function testAReleaseNamingAMachineDoesNotInheritTheProductionsMachines(): void
    {
        // the production runs on both, this release is the 128K-only one
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn(['zx48', 'zx128', 'ay']);

        $effective = $this->service->getEffectiveCodes(20, ['zx128']);
        sort($effective);

        $this->assertSame(['ay', 'zx128'], $effective);
    }

    public function testAReleaseNamingNoMachineInheritsThemAll(): void
    {
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn(['zx48', 'zx128', 'ay']);

        $effective = $this->service->getEffectiveCodes(20, ['kempston']);
        sort($effective);

        $this->assertSame(['ay', 'kempston', 'zx128', 'zx48'], $effective);
    }

    /**
     * Gap-filling is per category, not machines-only: a release that lists
     * controls has listed the controls it supports, so the production's are not
     * added to them. The machine, which the release says nothing about, still
     * comes down.
     */
    public function testACategoryTheReleaseSpeaksAboutIsNotInherited(): void
    {
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn(['zx48', 'kempston']);

        $effective = $this->service->getEffectiveCodes(20, ['sinclair2']);
        sort($effective);

        $this->assertSame(['sinclair2', 'zx48'], $effective);
    }

    public function testEachCategoryIsDecidedOnItsOwn(): void
    {
        // production: a machine, a DOS and a sound chip; the release restates only the DOS
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn(['zx48', 'trdos', 'ay']);

        $effective = $this->service->getEffectiveCodes(20, ['3dos']);
        sort($effective);

        // the DOS is the release's own, the machine and the sound chip are inherited
        $this->assertSame(['3dos', 'ay', 'zx48'], $effective);
    }

    /**
     * The release page prints what comes back here as "the production's" chips.
     * It used to print the raw production set minus the release's own codes,
     * which put chips on the page for categories the release overrides and does
     * not actually carry. Because the applicable set can never overlap the
     * release's own codes, that subtraction is not merely wrong but unnecessary —
     * the page passes this straight through.
     */
    public function testWhatIsInheritedNeverOverlapsTheReleasesOwnCodes(): void
    {
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn(['zx48', 'trdos', 'ay', 'kempston']);

        $own = ['zx128', 'ay'];
        $applicable = $this->service->getInheritedApplicable(20, $own);

        $this->assertSame([], array_intersect($own, $applicable));
        // machines and sound are the release's own business, the rest comes down
        sort($applicable);
        $this->assertSame(['kempston', 'trdos'], $applicable);
    }

    public function testAReleaseWithNoHardwareInheritsEverything(): void
    {
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn(['zx48', 'ay']);

        $this->assertSame(['zx48', 'ay'], $this->service->getInheritedApplicable(20, []));
    }

    public function testAProductionWithNoHardwareGivesNothing(): void
    {
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn([]);

        $this->assertSame([], $this->service->getInheritedApplicable(20, ['zx48']));
    }

    public function testAggregatedCodesComeFromTheRepository(): void
    {
        $this->repository->method('getAggregatedCodes')->with(10)->willReturn(['zx48', 'ay']);

        $this->assertSame(['zx48', 'ay'], $this->service->getAggregatedCodes(10));
    }

    public function testEffectiveCodesAreTheReleasesOwnPlusItsProds(): void
    {
        $this->repository->method('getProdCodesForRelease')->with(20)->willReturn(['cursor', 'kempston']);

        $this->assertSame(
            ['zx48', 'tape', 'cursor', 'kempston'],
            $this->service->getEffectiveCodes(20, ['zx48', 'tape']),
        );
    }

    public function testACodeOnBothSidesIsNotRepeated(): void
    {
        $this->repository->method('getProdCodesForRelease')->willReturn(['zx48', 'kempston']);

        $this->assertSame(['zx48', 'kempston'], $this->service->getEffectiveCodes(20, ['zx48']));
    }

    public function testAReleaseInheritingNothingKeepsItsOwnSet(): void
    {
        $this->repository->method('getProdCodesForRelease')->willReturn([]);

        $this->assertSame(['zx48'], $this->service->getEffectiveCodes(20, ['zx48']));
    }

    public function testTheRepositoryIsAskedOncePerElement(): void
    {
        // several components ask the same question while one page renders
        $this->repository->expects($this->once())
            ->method('getAggregatedCodes')
            ->willReturn(['zx48']);

        $this->service->getAggregatedCodes(10);
        $this->service->getAggregatedCodes(10);
    }

    public function testForgettingAnElementMakesItAskAgain(): void
    {
        $this->repository->expects($this->exactly(2))
            ->method('getAggregatedCodes')
            ->willReturn(['zx48']);

        $this->service->getAggregatedCodes(10);
        $this->service->forget(10);
        $this->service->getAggregatedCodes(10);
    }

    public function testAnUnsavedElementIsNeverQueried(): void
    {
        $this->repository->expects($this->never())->method('getAggregatedCodes');
        $this->repository->expects($this->never())->method('getProdCodesForRelease');

        $this->assertSame([], $this->service->getAggregatedCodes(0));
        $this->assertSame([], $this->service->getInheritedCodes(0));
        $this->assertSame(['zx48'], $this->service->getEffectiveCodes(0, ['zx48']));
    }
}
