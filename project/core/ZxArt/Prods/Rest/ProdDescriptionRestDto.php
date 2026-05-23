<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdDescriptionRestDto
{
    public function __construct(
        public string $description,
        public bool $htmlDescription,
        public string $instructions,
    ) {
    }
}
