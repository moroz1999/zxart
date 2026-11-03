<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

/**
 * @psalm-api
 */
final readonly class HardwareListDto
{
    /**
     * @param list<HardwareGroupDto> $groups
     */
    public function __construct(
        public array $groups,
    ) {
    }
}
