<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Catalogues;

use ZxArt\Tags\Dto\FoundTagDto;

/**
 * @psalm-api
 */
final readonly class FoundTagsDto
{
    /**
     * @param list<FoundTagDto> $tags the most used first
     */
    public function __construct(
        public array $tags,
    ) {
    }
}
