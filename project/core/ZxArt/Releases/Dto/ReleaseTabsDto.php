<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Releases\Rest\ReleaseTabsRestDto;

#[Map(target: ReleaseTabsRestDto::class)]
readonly class ReleaseTabsDto
{
    public function __construct(
        public bool $hasScreenshots,
        public bool $hasInlays,
        public bool $hasInstructions,
        public bool $hasStructure,
        public bool $hasPictures,
    ) {
    }
}
