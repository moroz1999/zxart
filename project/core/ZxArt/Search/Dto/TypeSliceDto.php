<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

use ZxArt\Search\Services\PhraseSearchType;

/**
 * The part of one type's ordered results a page takes.
 */
final readonly class TypeSliceDto
{
    public function __construct(
        public PhraseSearchType $type,
        public int $offset,
        public int $limit,
    ) {
    }
}
