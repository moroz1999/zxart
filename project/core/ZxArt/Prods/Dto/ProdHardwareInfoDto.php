<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdHardwareInfoRestDto;

/**
 * One hardware item as it appears on a production or release.
 *
 * Carries its own labels: hardware names live in the editable catalog, so the
 * SPA no longer owns them. `name` is the full label for detail pages, `shortName`
 * the compact one for cards and badges, and `category` lets the frontend group
 * items and pick a fallback icon for a code it does not know yet.
 */
#[Map(target: ProdHardwareInfoRestDto::class)]
readonly class ProdHardwareInfoDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $shortName,
        public string $category,
    ) {
    }
}
