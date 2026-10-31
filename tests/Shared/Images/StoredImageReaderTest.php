<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared\Images;

use fileElement;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Shared\Images\ImageDto;
use ZxArt\Shared\Images\ImageRefusedException;
use ZxArt\Shared\Images\StoredImageReader;
use ZxArt\ZxScreen\ZxScreenRenderer;
use zxProdElement;

final class StoredImageReaderTest extends TestCase
{
    private const string PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private string $path;

    protected function setUp(): void
    {
        $this->path = (string)tempnam(sys_get_temp_dir(), 'image');
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testAStoredImageComesWithItsTypeInBase64(): void
    {
        file_put_contents($this->path, base64_decode(self::PNG));

        self::assertEquals(new ImageDto('image/png', self::PNG), $this->reader($this->file($this->path))->read(7));
    }

    public function testAZxScreenComesConvertedAsTheSiteShowsIt(): void
    {
        file_put_contents($this->path, str_repeat("\x00", 6912));

        $image = $this->reader($this->file($this->path, 'standard'))->read(7);

        self::assertSame('image/png', $image->mediaType);
        self::assertSame([512, 384], array_slice((array)getimagesizefromstring(base64_decode($image->data)), 0, 2));
    }

    public function testAFileThatIsNotAnImageIsRefused(): void
    {
        file_put_contents($this->path, "\x13\x00\x00\x03INTRO     ");
        $this->expectException(ImageRefusedException::class);

        $this->reader($this->file($this->path))->read(7);
    }

    public function testAFileMissingOnDiskIsRefused(): void
    {
        unlink($this->path);
        $this->expectException(ImageRefusedException::class);

        $this->reader($this->file($this->path))->read(7);
    }

    public function testAnIdOfSomethingElseIsRefused(): void
    {
        $this->expectException(ImageRefusedException::class);

        $this->reader($this->createStub(zxProdElement::class))->read(7);
    }

    private function file(string $path, ?string $zxScreenType = null): fileElement
    {
        $file = $this->createStub(fileElement::class);
        $file->method('getFilePath')->willReturn($path);
        $file->method('getZxScreenType')->willReturn($zxScreenType);

        return $file;
    }

    private function reader(?object $element): StoredImageReader
    {
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->method('getElementById')->with(7)->willReturn($element);

        return new StoredImageReader($structureManager, new ZxScreenRenderer());
    }
}
