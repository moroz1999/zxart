<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly class GroupLinkRestDto
{
    public function __construct(
        public string $url,
        public string $label,
    ) {
    }
}
