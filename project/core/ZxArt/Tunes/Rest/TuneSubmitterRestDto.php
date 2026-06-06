<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Rest;

readonly class TuneSubmitterRestDto
{
    public function __construct(
        public string $userName,
        public string $url,
    ) {
    }
}
