<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly class GroupRefRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $years,
    ) {
    }
}
