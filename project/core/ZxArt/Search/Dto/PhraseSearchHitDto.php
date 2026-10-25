<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

use structureElement;

final readonly class PhraseSearchHitDto
{
    /**
     * @param string $type the structure type it was searched as: an alias merged into its owner comes as the owner's
     */
    public function __construct(
        public string $type,
        public structureElement $element,
    ) {
    }
}
