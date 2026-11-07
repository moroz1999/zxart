<?php

declare(strict_types=1);

namespace ZxArt\Tests\Pictures;

use PHPUnit\Framework\TestCase;
use ZxArt\Pictures\Validation\NativePictureFileError;
use ZxArt\Pictures\Validation\NativePictureFileValidator;

final class NativePictureFileValidatorTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $temporaryFile) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    public function testStandardFormatRequiresKnownExtensionsAndExactSize(): void
    {
        $rule = (new NativePictureFileValidator())->getRules()['standard'];

        self::assertSame(['scr', 'bin', 'c'], $rule['extensions']);
        self::assertSame([6912], $rule['sizes']);
    }

    public function testStandardFormatAcceptsEveryKnownExtensionCaseInsensitively(): void
    {
        $validator = new NativePictureFileValidator();
        $file = $this->fileOfSize(6912);

        self::assertNull($validator->validate('standard', 'picture.scr', $file));
        self::assertNull($validator->validate('standard', 'picture.BIN', $file));
        self::assertNull($validator->validate('standard', 'picture.C', $file));
    }

    public function testStandardFormatRejectsUnknownExtensionEvenWithCorrectSize(): void
    {
        $file = $this->fileOfSize(6912);

        $error = (new NativePictureFileValidator())->validate('standard', 'picture.raw', $file);

        self::assertSame(NativePictureFileError::Extension, $error);
    }

    public function testStandardFormatRejectsWrongSizeEvenWithKnownExtension(): void
    {
        $file = $this->fileOfSize(6911);

        $error = (new NativePictureFileValidator())->validate('standard', 'picture.scr', $file);

        self::assertSame(NativePictureFileError::Size, $error);
    }

    public function testPcImageSignatureIsRejectedAfterNativeRename(): void
    {
        $file = $this->fileOfSize(6912, "\x89PNG\r\n\x1a\n");

        $error = (new NativePictureFileValidator())->validate('standard', 'renamed.scr', $file);

        self::assertSame(NativePictureFileError::PcImage, $error);
    }

    public function testBmpExtensionBelongsOnlyToZxEvo(): void
    {
        $validator = new NativePictureFileValidator();

        self::assertSame(
            NativePictureFileError::Extension,
            $validator->validate('standard', 'picture.bmp', $this->fileOfSize(6912)),
        );
        self::assertSame(['bmp'], $validator->getRules()['zxevo']['extensions']);
        self::assertSame([], $validator->getRules()['zxevo']['sizes']);
    }

    public function testEverySelectableFormatHasTheDocumentedSizeRule(): void
    {
        $expectedSizes = [
            'standard' => [6912],
            'flash' => [6912],
            'monochrome' => [6144],
            'tricolor' => [18432],
            'multicolor' => [9216],
            'multicolor4' => [7680],
            'timex81' => [12288],
            'timexhr' => [12289],
            'timexhrg' => [24578],
            'sam2' => [12288],
            'sam3' => [24580, 24617],
            'sam4' => [24592, 24617, 24681],
            'bsc' => [11136],
            'bsp' => [],
            'bmc4' => [11904],
            'mlt' => [12288],
            'mc' => [12288],
            'gigascreen' => [13824],
            'lce' => [13824],
            'mg1' => [19456],
            'mg2' => [18688],
            'mg4' => [15616],
            'mg8' => [14080],
            'attributes' => [768],
            'lowresgs' => [1628],
            'stellar' => [3072],
            'chr$' => [],
            'ulaplus' => [6976],
            'zxevo' => [],
            'sxg' => [],
            'grf' => [],
            'nxi' => [49664],
            'sl2' => [49152, 49280, 49408, 49664, 81920, 81936, 81952, 82176, 82432],
            'specscii' => [],
            's80' => [768],
            's81' => [768],
            'sca' => [],
        ];

        $actualSizes = array_map(
            static fn(array $rule): array => $rule['sizes'],
            (new NativePictureFileValidator())->getRules(),
        );

        self::assertSame($expectedSizes, $actualSizes);
    }

    private function fileOfSize(int $size, string $prefix = ''): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'zxart-picture-test-');
        self::assertNotFalse($temporaryFile);
        $contents = $prefix . str_repeat("\0", $size - strlen($prefix));
        self::assertSame($size, file_put_contents($temporaryFile, $contents));
        $this->temporaryFiles[] = $temporaryFile;

        return $temporaryFile;
    }
}
