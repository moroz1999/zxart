<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Hardware\Rest\HardwareItemRestDto;
use ZxArt\Shared\InterfaceLanguage;
use ZxArt\Shared\ObjectMapper\BackedEnumValue;

/**
 * A hardware item of the editable catalog.
 *
 * `code` is the stable identifier every other layer works with — element
 * properties, forms, REST responses and the public `/api/` filters all speak
 * codes. The numeric `id` exists only because the link tables reference it.
 */
#[Map(target: HardwareItemRestDto::class)]
readonly class HardwareItemDto
{
    /**
     * @param array<string, HardwareNameDto> $names keyed by {@see InterfaceLanguage} value
     * @param int $usages how many releases and productions reference this item;
     *        the management screen shows it and deletion refuses while it is > 0
     */
    public function __construct(
        public int $id,
        public string $code,
        // Two ObjectMapper details, both easy to get wrong: property transforms
        // are read from the class carrying the class-level #[Map] (this one, not
        // the REST DTO), and a transform given as a class-string is silently
        // ignored unless a callable locator is configured — so it is instantiated.
        #[Map(transform: new BackedEnumValue())]
        public HardwareGroup $category,
        public int $position,
        #[Map(transform: new MapCollection())]
        public array $names,
        public int $usages = 0,
    ) {
    }

    public function getName(InterfaceLanguage $language): ?HardwareNameDto
    {
        return $this->names[$language->value] ?? null;
    }
}
