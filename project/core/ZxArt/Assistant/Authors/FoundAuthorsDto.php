<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Authors\Dto\FoundAuthorDto;

/**
 * @psalm-api
 */
final readonly class FoundAuthorsDto
{
    /**
     * @param list<FoundAuthorDto> $results
     */
    public function __construct(
        public int $total,
        public array $results,
    ) {
    }
}
