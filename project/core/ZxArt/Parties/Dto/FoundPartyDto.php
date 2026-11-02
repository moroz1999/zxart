<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

/**
 * A party a phrase found.
 *
 * @psalm-api
 */
final readonly class FoundPartyDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $abbreviation,
        /** the year of the event, null when the party is not under a year */
        public ?int $year,
    ) {
    }
}
