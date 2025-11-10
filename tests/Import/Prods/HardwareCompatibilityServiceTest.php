<?php
declare(strict_types=1);

namespace Tests\Import\Prods;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Prods\HardwareCompatibilityService;
use zxProdElement;

final class HardwareCompatibilityServiceTest extends TestCase
{
    private HardwareCompatibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HardwareCompatibilityService();
    }

    /**
     * @return zxProdElement&MockObject
     */
    private function makeProdWithReleases(array $hardwarePerRelease): zxProdElement
    {
        /** @var zxProdElement&MockObject $prod */
        $prod = $this->getMockBuilder(zxProdElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getReleasesList'])
            ->getMock();

        $releases = [];
        foreach ($hardwarePerRelease as $hardware) {
            $releases[] = (object)['hardwareRequired' => $hardware];
        }

        $prod->method('getReleasesList')->willReturn($releases);
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

    public function testDtoLacksHardwareProdHasHardwareReturnsFalse(): void
    {
        $dto = $this->makeDtoWithReleases([[/* empty */], []]); // empty hardware in all releases
        $prod = $this->makeProdWithReleases([["zx48"]]);

        $this->assertFalse($this->service->areProdAndDtoCompatible($dto, $prod));
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
}
