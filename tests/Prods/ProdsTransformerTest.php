<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Prods\ProdsTransformer;
use zxProdElement;

/**
 * Which hardware set a production's card is built from.
 *
 * Only `buildHardwareInfo()` is exercised, through reflection: `toDto()` reads a
 * dozen magic properties off the legacy element, which a mock cannot carry. The
 * narrow target is deliberate — this pins one regression, not the DTO shape.
 */
#[AllowMockObjectsWithoutExpectations]
class ProdsTransformerTest extends TestCase
{
    /**
     * Cards used to be built from `getAggregatedHardwareCodes()`, so a plainly
     * 48K production advertised the GS soundtrack and the microdrive edition of
     * two of its sixteen releases. A production is described by what its releases
     * share; the aggregate is for matching, not for labelling.
     */
    public function testTheCardShowsTheProductionsOwnSetAndNeverTheAggregate(): void
    {
        $element = $this->createMock(zxProdElement::class);
        $element->method('getHardwareCodes')->willReturn(['zx48', 'ay']);
        $element->expects($this->never())->method('getAggregatedHardwareCodes');

        $this->assertSame(['zx48', 'ay'], array_column($this->buildHardwareInfo($element), 'id'));
    }

    public function testLabelsComeFromTheCatalogInTheRequestLanguage(): void
    {
        $element = $this->createMock(zxProdElement::class);
        $element->method('getHardwareCodes')->willReturn(['zx48']);

        $this->assertSame(
            [['id' => 'zx48', 'name' => 'ZX Spectrum 48K', 'shortName' => '48', 'category' => 'computers']],
            $this->buildHardwareInfo($element),
        );
    }

    public function testACodeTheCatalogNoLongerKnowsFallsBackToItself(): void
    {
        $element = $this->createMock(zxProdElement::class);
        $element->method('getHardwareCodes')->willReturn(['retired_code']);

        $this->assertSame(
            [['id' => 'retired_code', 'name' => 'retired_code', 'shortName' => 'retired_code', 'category' => '']],
            $this->buildHardwareInfo($element),
        );
    }

    /**
     * @return array<array{id: string, name: string, shortName: string, category: string}>
     */
    private function buildHardwareInfo(zxProdElement $element): array
    {
        $catalog = $this->createMock(HardwareCatalogService::class);
        $catalog->method('getLabels')->willReturn([
            'zx48' => ['name' => 'ZX Spectrum 48K', 'shortName' => '48'],
            'ay' => ['name' => 'AY-3-8910/12', 'shortName' => 'AY'],
        ]);
        $catalog->method('getCategoryOf')->willReturnCallback(
            static fn(string $code): ?HardwareGroup => match ($code) {
                'zx48' => HardwareGroup::COMPUTERS,
                'ay' => HardwareGroup::SOUND,
                default => null,
            },
        );

        $method = new ReflectionMethod(ProdsTransformer::class, 'buildHardwareInfo');

        return $method->invoke(new ProdsTransformer($catalog), $element);
    }
}
