<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Catalogues;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Catalogues\HardwareGroupDto;
use ZxArt\Assistant\Tools\Catalogues\HardwareListDto;
use ZxArt\Assistant\Tools\Catalogues\HardwareOptionDto;
use ZxArt\Assistant\Tools\Catalogues\ListHardwareRequestDto;
use ZxArt\Assistant\Tools\Catalogues\ListHardwareTool;
use ZxArt\Hardware\HardwareCatalogService;

final class ListHardwareToolTest extends TestCase
{
    public function testItIsNamedListHardware(): void
    {
        $tool = new ListHardwareTool($this->createStub(HardwareCatalogService::class));

        self::assertSame('list_hardware', $tool->name());
        self::assertSame(ListHardwareRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersTheCodesByGroupWithTheirNames(): void
    {
        $catalog = $this->createStub(HardwareCatalogService::class);
        $catalog->method('getGroupedCodes')->willReturn(['computers' => ['zx48', 'zx128'], 'sound' => ['ay']]);
        $catalog->method('getLabels')->willReturn([
            'zx48' => ['name' => 'ZX Spectrum 48K', 'shortName' => '48K'],
            'zx128' => ['name' => 'ZX Spectrum 128K', 'shortName' => '128K'],
            'ay' => ['name' => 'AY-3-8910', 'shortName' => 'AY'],
        ]);

        self::assertEquals(new HardwareListDto([
            new HardwareGroupDto('computers', [new HardwareOptionDto('zx48', 'ZX Spectrum 48K'), new HardwareOptionDto('zx128', 'ZX Spectrum 128K')]),
            new HardwareGroupDto('sound', [new HardwareOptionDto('ay', 'AY-3-8910')]),
        ]), (new ListHardwareTool($catalog))->execute(new ListHardwareRequestDto()));
    }
}
