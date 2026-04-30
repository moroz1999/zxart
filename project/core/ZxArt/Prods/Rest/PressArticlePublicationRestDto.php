<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class PressArticlePublicationRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?int $year,
    ) {
    }
}
