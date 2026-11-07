<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

use structureElement;
use ZxArt\Search\Services\PhraseSearchType;

final readonly class PhraseSearchHitDto
{
    /**
     * @param PhraseSearchType $type the type it was searched as: an alias merged into its owner comes as the owner's
     */
    public function __construct(
        public PhraseSearchType $type,
        public structureElement $element,
    ) {
    }
}
