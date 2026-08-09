<?php
declare(strict_types=1);

namespace Tests\Import\Prods;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Prods\HardwareCompatibilityService;
use zxProdElement;

#[AllowMockObjectsWithoutExpectations]
final class HardwareCompatibilityServiceTest extends TestCase
{
    private HardwareCompatibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HardwareCompatibilityService();
    }

    /**
     * The production side is compared as one flat set — its own hardware plus
     * every release's — so the double only has to answer that.
     *
     * @return zxProdElement&MockObject
     */
    private function makeProdWithReleases(array $hardwarePerRelease): zxProdElement
    {
        return $this->makeProd(array_merge([], ...array_values($hardwarePerRelease)));
    }

    /**
     * @param list<string> $aggregatedHardware
     * @return zxProdElement&MockObject
     */
    private function makeProd(array $aggregatedHardware): zxProdElement
    {
        /** @var zxProdElement&MockObject $prod */
        $prod = $this->getMockBuilder(zxProdElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAggregatedHardwareCodes'])
            ->getMock();

        $prod->method('getAggregatedHardwareCodes')->willReturn(array_values(array_unique($aggregatedHardware)));

        return $prod;
    }

    private function makeDtoWithReleases(array $hardwarePerRelease): ProdImportDTO
    {
        $releases = [];
        $i = 1;
        foreach ($hardwarePerRelease as $hardware) {
            $releases[] = new ReleaseImportDTO(id: (string)$i, title: 'r'.$i, hardwareRequired: $hardware);
            $i++;
        }
        return new ProdImportDTO(id: 'dto', title: 'T', releases: $releases);
    }

    public function testBothSidesWithoutHardwareReturnsFalse(): void
    {
        $dto = new ProdImportDTO(id: 'dto', title: 'T', releases: null);
        $prod = $this->makeProdWithReleases([]);

        $this->assertFalse($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testDtoLacksHardwareProdHasHardwareReturnsTrue(): void
    {
        // When DTO has no hardware info but prod does, return true (vtrdos import compatibility)
        $dto = $this->makeDtoWithReleases([[/* empty */], []]); // empty hardware in all releases
        $prod = $this->makeProdWithReleases([["zx48"]]);

        $this->assertTrue($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testDtoHasHardwareProdLacksHardwareReturnsFalse(): void
    {
        $dto = $this->makeDtoWithReleases([["zx48"]]);
        $prod = $this->makeProdWithReleases([[/* empty */], []]);

        $this->assertFalse($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testOverlappingGroupsReturnsTrue(): void
    {
        // dto: zx128 -> group zx48; prod: timex2068 -> group zx48
        $dto = $this->makeDtoWithReleases([["zx128"]]);
        $prod = $this->makeProdWithReleases([["timex2068"]]);

        $this->assertTrue($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testNoOverlapReturnsFalse(): void
    {
        // dto: zx80; prod: zx81 -> different groups
        $dto = $this->makeDtoWithReleases([["zx80"]]);
        $prod = $this->makeProdWithReleases([["zx81"]]);

        $this->assertFalse($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testUnknownCodesIgnoredAndLeadToFalse(): void
    {
        // dto has only unknown code -> groups empty -> no intersection
        $dto = $this->makeDtoWithReleases([["unknown-hw-code"]]);
        $prod = $this->makeProdWithReleases([["zx48"]]);

        $this->assertFalse($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testMultipleReleasesAnyOverlapWins(): void
    {
        $dto = $this->makeDtoWithReleases([[], ["zxuno"]]);
        $prod = $this->makeProdWithReleases([["samcoupe"], ["zxuno"]]);

        $this->assertTrue($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testProdLevelHardwareOnTheDtoCounts(): void
    {
        // ZxDB and World of Sam put hardware on the production, not on its releases
        $dto = new ProdImportDTO(id: 'dto', title: 'T', hardwareRequired: ['zx128'], releases: null);
        $prod = $this->makeProd(['timex2068']);

        $this->assertTrue($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    /**
     * The regression the whole D.7 rerouting exists for: once the shared codes
     * have been moved off a production's releases, re-importing it must still
     * recognise it. Comparing release by release would see empty releases, reject
     * the match, and make the importer create a duplicate production.
     */
    public function testAMigratedProdStillMatchesItsOwnReimport(): void
    {
        $dto = new ProdImportDTO(id: 'dto', title: 'T', hardwareRequired: ['zx48'], releases: null);
        // hardware now sits on the production; its releases carry nothing
        $prod = $this->makeProd(['zx48']);

        $this->assertTrue($this->service->areProdAndDtoCompatible($dto, $prod));
    }

    public function testAProdOfADifferentMachineIsStillRejected(): void
    {
        $dto = new ProdImportDTO(id: 'dto', title: 'T', hardwareRequired: ['zx48'], releases: null);
        $prod = $this->makeProd(['samcoupe']);

        $this->assertFalse($this->service->areProdAndDtoCompatible($dto, $prod));
    }
}
