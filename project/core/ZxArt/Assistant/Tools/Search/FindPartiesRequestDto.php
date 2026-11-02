<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

/**
 * @psalm-api
 */
final readonly class FindPartiesRequestDto
{
    /**
     * @param string $phrase a part of the party's title, abbreviation or original name
     * @param int|null $year the year of the party
     * @param int $limit the most parties to answer, up to 100
     */
    public function __construct(
        public string $phrase,
        public ?int $year = null,
        public int $limit = 20,
    ) {
    }
}
