<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

readonly class PictureProdContextRestDto
{
    public function __construct(
        public string $title,
        public string $url,
        public ?string $year,
    ) {
    }
}
