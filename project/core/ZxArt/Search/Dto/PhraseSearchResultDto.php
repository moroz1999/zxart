<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

final readonly class PhraseSearchResultDto
{
    /**
     * @param int $total matches of all types, beyond the page too
     * @param PhraseSearchHitDto[] $hits the page, in order
     */
    public function __construct(
        public int $total,
        public array $hits,
    ) {
    }
}
