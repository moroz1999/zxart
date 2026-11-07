<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Validation;

final readonly class NativePictureFileValidator
{
    /**
     * Empty size lists mean that the format carries its size in its own structure.
     *
     * @var array<string, array{extensions: list<string>, sizes: list<int>}>
     */
    private const array RULES = [
        'standard' => ['extensions' => ['scr', 'bin', 'c'], 'sizes' => [6912]],
        'flash' => ['extensions' => ['scr'], 'sizes' => [6912]],
        'monochrome' => ['extensions' => ['bin', 'scr', 'c'], 'sizes' => [6144]],
        'tricolor' => ['extensions' => ['rgb', '3', 'c'], 'sizes' => [18432]],
        'multicolor' => ['extensions' => ['ifl', 'c'], 'sizes' => [9216]],
        'multicolor4' => ['extensions' => ['ifl'], 'sizes' => [7680]],
        'timex81' => ['extensions' => ['scr', 'mlt'], 'sizes' => [12288]],
        'timexhr' => ['extensions' => ['scr'], 'sizes' => [12289]],
        'timexhrg' => ['extensions' => ['hrg'], 'sizes' => [24578]],
        'sam2' => ['extensions' => ['ss2'], 'sizes' => [12288]],
        'sam3' => ['extensions' => ['ss3', 'ssx'], 'sizes' => [24580, 24617]],
        'sam4' => ['extensions' => ['ss4', 'ssx', 'scr'], 'sizes' => [24592, 24617, 24681]],
        'bsc' => ['extensions' => ['bsc', 'scr'], 'sizes' => [11136]],
        'bsp' => ['extensions' => ['bsp'], 'sizes' => []],
        'bmc4' => ['extensions' => ['bmc4'], 'sizes' => [11904]],
        'mlt' => ['extensions' => ['mlt'], 'sizes' => [12288]],
        'mc' => ['extensions' => ['mc'], 'sizes' => [12288]],
        'gigascreen' => ['extensions' => ['img', 'gsc', 'scr', 'c'], 'sizes' => [13824]],
        'lce' => ['extensions' => ['lce'], 'sizes' => [13824]],
        'mg1' => ['extensions' => ['mg1', 'c'], 'sizes' => [19456]],
        'mg2' => ['extensions' => ['mg2', 'c'], 'sizes' => [18688]],
        'mg4' => ['extensions' => ['mg4'], 'sizes' => [15616]],
        'mg8' => ['extensions' => ['mg8', 'c'], 'sizes' => [14080]],
        'attributes' => ['extensions' => ['atr', 'attr', 'c', 'scr'], 'sizes' => [768]],
        'lowresgs' => ['extensions' => ['hlr', 'c'], 'sizes' => [1628]],
        'stellar' => ['extensions' => ['stl', 'c', 'bin'], 'sizes' => [3072]],
        'chr$' => ['extensions' => ['chr$', 'ch$', '$c'], 'sizes' => []],
        'ulaplus' => ['extensions' => ['scr'], 'sizes' => [6976]],
        'zxevo' => ['extensions' => ['bmp'], 'sizes' => []],
        'sxg' => ['extensions' => ['sxg'], 'sizes' => []],
        'grf' => ['extensions' => ['grf'], 'sizes' => []],
        'nxi' => ['extensions' => ['nxi'], 'sizes' => [49664]],
        'sl2' => [
            'extensions' => ['sl2'],
            'sizes' => [49152, 49280, 49408, 49664, 81920, 81936, 81952, 82176, 82432],
        ],
        'specscii' => ['extensions' => ['specscii', 'c'], 'sizes' => []],
        's80' => ['extensions' => ['s80'], 'sizes' => [768]],
        's81' => ['extensions' => ['s81'], 'sizes' => [768]],
        'sca' => ['extensions' => ['sca'], 'sizes' => []],
    ];

    /** @return array<string, array{extensions: list<string>, sizes: list<int>}> */
    public function getRules(): array
    {
        return self::RULES;
    }

    public function validate(string $format, string $fileName, string $filePath): ?NativePictureFileError
    {
        $rule = self::RULES[$format] ?? null;
        if ($rule === null) {
            return NativePictureFileError::UnsupportedFormat;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($extension, $rule['extensions'], true)) {
            return NativePictureFileError::Extension;
        }
        if (!is_file($filePath) || !is_readable($filePath)) {
            return NativePictureFileError::Content;
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false) {
            return NativePictureFileError::Content;
        }
        if ($rule['sizes'] !== [] && !in_array($fileSize, $rule['sizes'], true)) {
            return NativePictureFileError::Size;
        }

        $header = file_get_contents($filePath, false, null, 0, 64);
        if ($header === false) {
            return NativePictureFileError::Content;
        }
        if ($format !== 'zxevo' && $this->isPcImage($header)) {
            return NativePictureFileError::PcImage;
        }

        return $format === 'zxevo' ? $this->validateZxEvoBmp($header) : null;
    }

    private function isPcImage(string $header): bool
    {
        return str_starts_with($header, "\x89PNG\r\n\x1a\n")
            || str_starts_with($header, 'GIF87a')
            || str_starts_with($header, 'GIF89a')
            || str_starts_with($header, "\xff\xd8\xff")
            || str_starts_with($header, 'BM');
    }

    private function validateZxEvoBmp(string $header): ?NativePictureFileError
    {
        if (strlen($header) < 54 || !str_starts_with($header, 'BM')) {
            return NativePictureFileError::Content;
        }

        $dibHeaderSize = unpack('Vvalue', substr($header, 14, 4));
        $bitsPerPixel = unpack('vvalue', substr($header, 28, 2));
        $colorsUsed = unpack('Vvalue', substr($header, 46, 4));
        if ($dibHeaderSize === false || $bitsPerPixel === false || $colorsUsed === false) {
            return NativePictureFileError::Content;
        }
        if ($dibHeaderSize['value'] < 40 || $bitsPerPixel['value'] === 0) {
            return NativePictureFileError::Content;
        }

        $colorsAmount = $colorsUsed['value'];
        if ($colorsAmount === 0 && $bitsPerPixel['value'] <= 8) {
            $colorsAmount = 2 ** $bitsPerPixel['value'];
        }

        return $colorsAmount > 0 && $colorsAmount <= 16
            ? null
            : NativePictureFileError::Content;
    }
}
