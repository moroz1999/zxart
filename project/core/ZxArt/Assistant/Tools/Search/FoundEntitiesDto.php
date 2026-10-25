<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use ZxArt\Search\Dto\FoundEntityDto;

/**
 * @psalm-api
 */
final readonly class FoundEntitiesDto
{
    /**
     * @param FoundEntityDto[] $results the closest titles first
     */
    public function __construct(
        public array $results,
    ) {
    }
}
