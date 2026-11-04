<?php

declare(strict_types=1);

namespace ZxArt\Screenshots;

use ZxArt\Hardware\HardwareItem;

final readonly class ListImagePresetResolver
{
    public const string CROP_PRESET = 'prodListImage';
    public const string COVER_PRESET = 'prodListImageCover';

    private const int ENLARGED_WIDTH = 512;
    private const int ENLARGED_HEIGHT = 384;

    /**
     * @param list<string> $hardwareCodes
     */
    public function resolve(array $hardwareCodes, ?int $sourceWidth, ?int $sourceHeight): string
    {
        if ($this->hasExtendedGraphics($hardwareCodes)) {
            return self::COVER_PRESET;
        }

        if ($sourceWidth === null || $sourceHeight === null) {
            return self::CROP_PRESET;
        }

        if ($sourceWidth >= self::ENLARGED_WIDTH || $sourceHeight >= self::ENLARGED_HEIGHT) {
            return self::COVER_PRESET;
        }

        return self::CROP_PRESET;
    }

    /**
     * @param list<string> $hardwareCodes
     */
    private function hasExtendedGraphics(array $hardwareCodes): bool
    {
        foreach ($hardwareCodes as $hardwareCode) {
            $hardware = HardwareItem::tryFrom($hardwareCode);
            $usesExtendedGraphics = match ($hardware) {
                HardwareItem::SINCLAIRQL,
                HardwareItem::BASECONF,
                HardwareItem::TSCONF,
                HardwareItem::ZXNEXT,
                HardwareItem::ELEMENTZXMB,
                HardwareItem::ZXUNO,
                HardwareItem::GMX => true,
                default => $hardwareCode === 'zxevolution',
            };

            if ($usesExtendedGraphics) {
                return true;
            }
        }

        return false;
    }
}
