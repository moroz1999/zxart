<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly class GroupLocationItemRestDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
