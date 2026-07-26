<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Rest;

readonly class TuneTagRestDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
