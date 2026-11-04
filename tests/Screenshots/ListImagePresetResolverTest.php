<?php

declare(strict_types=1);

namespace ZxArt\Tests\Screenshots;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Screenshots\ListImagePresetResolver;

class ListImagePresetResolverTest extends TestCase
{
    private const string CROP_PRESET = 'prodListImage';
    private const string COVER_PRESET = 'prodListImageCover';

    public function testNativeClassicScreenshotUsesCropPreset(): void
    {
        $resolver = new ListImagePresetResolver();

        self::assertSame(self::CROP_PRESET, $resolver->resolve(['zx48'], 256, 192));
    }

    public function testClassicScreenshotWithBorderUsesCropPreset(): void
    {
        $resolver = new ListImagePresetResolver();

        self::assertSame(self::CROP_PRESET, $resolver->resolve(['zx48'], 320, 240));
    }

    #[DataProvider('enlargedClassicScreenshotProvider')]
    public function testEnlargedClassicScreenshotUsesCoverPreset(int $width, int $height): void
    {
        $resolver = new ListImagePresetResolver();

        self::assertSame(self::COVER_PRESET, $resolver->resolve(['zx48'], $width, $height));
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function enlargedClassicScreenshotProvider(): iterable
    {
        yield 'two times' => [512, 384];
        yield 'three times' => [768, 576];
    }

    #[DataProvider('extendedGraphicsHardwareProvider')]
    public function testExtendedGraphicsHardwareUsesCoverPreset(string $hardwareCode): void
    {
        $resolver = new ListImagePresetResolver();

        self::assertSame(self::COVER_PRESET, $resolver->resolve([$hardwareCode], 256, 192));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function extendedGraphicsHardwareProvider(): iterable
    {
        yield 'Sinclair QL' => ['sinclairql'];
        yield 'ZX Evolution BaseConf' => ['baseconf'];
        yield 'TSConf' => ['tsconf'];
        yield 'ZX Spectrum Next' => ['zxnext'];
    }

    public function testClassicNativeScreenWithoutRasterDimensionsUsesCropPreset(): void
    {
        $resolver = new ListImagePresetResolver();

        self::assertSame(self::CROP_PRESET, $resolver->resolve(['zx128'], null, null));
    }
}
