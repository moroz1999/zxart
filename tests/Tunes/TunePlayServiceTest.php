<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tunes;

use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Tunes\Exception\TuneNotFoundException;
use ZxArt\Tunes\Services\TunePlayService;
use zxMusicElement;

class TunePlayServiceTest extends TestCase
{
    public function testLogPlayCallsElement(): void
    {
        $element = $this->createMock(zxMusicElement::class);
        $element->expects($this->once())->method('logPlay');

        $structureManager = $this->createMock(structureManager::class);
        $structureManager->method('getElementById')->with(10)->willReturn($element);

        $service = new TunePlayService($structureManager);

        $service->logPlay(10);
    }

    public function testLogPlayThrowsWhenElementMissing(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->method('getElementById')->with(10)->willReturn(null);

        $service = new TunePlayService($structureManager);

        $this->expectException(TuneNotFoundException::class);
        $service->logPlay(10);
    }
}
