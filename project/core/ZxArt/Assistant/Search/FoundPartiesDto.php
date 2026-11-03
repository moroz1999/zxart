<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Search;

use ZxArt\Parties\Dto\FoundPartyDto;

/**
 * @psalm-api
 */
final readonly class FoundPartiesDto
{
    /**
     * @param list<FoundPartyDto> $parties by title
     */
    public function __construct(
        public array $parties,
    ) {
    }
}
