<?php

declare(strict_types=1);

namespace ZxArt\Tags\Rest;

final readonly class TagLookupItemRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $value,
        public string $synonym,
        public string $description,
        public string $url,
    ) {
    }
}
