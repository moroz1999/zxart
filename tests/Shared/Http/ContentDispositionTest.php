<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared\Http;

use PHPUnit\Framework\TestCase;
use ZxArt\Shared\Http\ContentDisposition;

class ContentDispositionTest extends TestCase
{
    public function testReadsAQuotedFileName(): void
    {
        self::assertSame('ZX Spectrum English.zip', ContentDisposition::fileName('attachment; filename="ZX Spectrum English.zip"'));
    }

    public function testReadsAnEncodedFileNameFirst(): void
    {
        self::assertSame('Español.tap', ContentDisposition::fileName("attachment; filename=\"Espanol.tap\"; filename*=UTF-8''Espa%C3%B1ol.tap"));
    }

    public function testAHeaderWithoutAFileNameNamesNothing(): void
    {
        self::assertNull(ContentDisposition::fileName(''));
        self::assertNull(ContentDisposition::fileName('inline'));
    }
}
