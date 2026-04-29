<?php

declare(strict_types=1);

namespace ZxArt\Tags\Rest;

readonly class TagRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description = null,
    ) {
    }
}
