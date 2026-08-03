<?php

declare(strict_types=1);

namespace ZxArt\Screenshots;

/**
 * Raw screen dump formats accepted by the screenshot upload endpoint. Each
 * format has a fixed byte size, so the uploaded body is validated by its length.
 */
enum ScreenshotFormat: string
{
    case Standard = 'standard';
    case Gigascreen = 'gigascreen';
    case S80 = 's80';
    case S81 = 's81';

    private const int SPECTRUM_SCREEN_SIZE = 6912;
    private const int ZX81_SCREEN_SIZE = 768;

    public function getSize(): int
    {
        return match ($this) {
            self::Standard => self::SPECTRUM_SCREEN_SIZE,
            self::Gigascreen => self::SPECTRUM_SCREEN_SIZE * 2,
            self::S80, self::S81 => self::ZX81_SCREEN_SIZE,
        };
    }

    public function getFileExtension(): string
    {
        return match ($this) {
            self::Standard => 'scr',
            self::Gigascreen => 'img',
            self::S80 => 's80',
            self::S81 => 's81',
        };
    }
}
