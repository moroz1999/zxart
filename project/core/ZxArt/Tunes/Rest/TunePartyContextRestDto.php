<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Rest;

readonly class TunePartyContextRestDto
{
    public function __construct(
        public string $title,
        public string $url,
        public ?int $place,
        public ?string $compoLabel,
    ) {
    }
}
