<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorTabsRestDto
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
