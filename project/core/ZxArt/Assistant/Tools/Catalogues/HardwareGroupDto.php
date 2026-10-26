<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Catalogues;

/**
 * @psalm-api
 */
final readonly class HardwareGroupDto
{
    /**
     * @param list<HardwareOptionDto> $items
     */
    public function __construct(
        public string $group,
        public array $items,
    ) {
    }
}
