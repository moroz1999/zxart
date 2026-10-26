<?php

declare(strict_types=1);

namespace ZxArt\Tags\Dto;

/**
 * A tag a phrase found, named as the row that matched names it.
 *
 * @psalm-api
 */
final readonly class FoundTagDto
{
    public function __construct(
        public int $id,
        public string $title,
        /** comma-separated */
        public string $synonyms,
        /** how many works carry the tag */
        public int $amount,
    ) {
    }
}
