<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorTabsRestDto;

#[Map(target: AuthorTabsRestDto::class)]
readonly class AuthorTabsDto
{
    public function __construct(
        public bool $hasPictures,
        public bool $hasTunes,
        public bool $hasProds,
        public bool $hasCollaborators,
        public bool $hasMentions,
    ) {
    }
}
