<?php

declare(strict_types=1);

namespace ZxArt\Releases\Rest;

readonly class ReleaseTabsRestDto
{
    public function __construct(
        public bool $hasScreenshots,
        public bool $hasInlays,
        public bool $hasInstructions,
    ) {
    }
}
