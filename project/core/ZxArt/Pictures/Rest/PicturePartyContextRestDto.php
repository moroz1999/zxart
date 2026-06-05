<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

readonly class PicturePartyContextRestDto
{
    public function __construct(
        public string $title,
        public string $url,
        public ?int $place,
        public ?string $compoLabel,
    ) {
    }
}
