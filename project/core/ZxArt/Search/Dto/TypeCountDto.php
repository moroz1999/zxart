<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

use ZxArt\Search\Services\PhraseSearchType;

/**
 * How many results a phrase has in one type.
 */
final readonly class TypeCountDto
{
    public function __construct(
        public PhraseSearchType $type,
        public int $count,
    ) {
    }
}
