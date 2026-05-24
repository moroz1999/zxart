<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorTechRestDto;

#[Map(target: AuthorTechRestDto::class)]
readonly class AuthorTechDto
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
