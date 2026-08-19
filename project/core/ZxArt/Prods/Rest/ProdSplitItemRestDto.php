<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdSplitItemRestDto
{
    public function __construct(
        public string $key,
        public string $title,
        public ?string $url,
        public ?string $imageUrl,
    ) {
    }
}
