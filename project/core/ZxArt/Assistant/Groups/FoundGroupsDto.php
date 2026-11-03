<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Groups\Dto\FoundGroupDto;

/**
 * @psalm-api
 */
final readonly class FoundGroupsDto
{
    /**
     * @param list<FoundGroupDto> $results
     */
    public function __construct(
        public int $total,
        public array $results,
    ) {
    }
}
