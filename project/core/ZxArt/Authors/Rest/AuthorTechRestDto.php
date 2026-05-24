<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorTechRestDto
{
    public function __construct(
        public string $palette,
        public string $ayChip,
        public string $ayChannels,
        public string $ayClock,
        public string $intFreq,
    ) {
    }
}
