<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use ZxArt\Releases\Dto\FoundReleaseDto;

/**
 * @psalm-api
 */
final readonly class FoundReleasesDto
{
    /**
     * @param list<FoundReleaseDto> $results
     */
    public function __construct(
        public int $total,
        public array $results,
    ) {
    }
}
