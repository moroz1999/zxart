<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Hardware\Rest\HardwareNameRestDto;
use ZxArt\Shared\InterfaceLanguage;
use ZxArt\Shared\ObjectMapper\BackedEnumValue;

/**
 * One hardware item's labels in one interface language.
 *
 * `name` is the full label used on detail pages and in forms, `shortName` the
 * compact one used on cards, badges and the release table. They differ for
 * about a third of the catalog ("ZX Spectrum 48K" vs "48").
 */
#[Map(target: HardwareNameRestDto::class)]
readonly class HardwareNameDto
{
    public function __construct(
        #[Map(transform: new BackedEnumValue())]
        public InterfaceLanguage $language,
        public string $name,
        public string $shortName,
    ) {
    }
}
