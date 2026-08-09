<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Rest;

readonly class HardwareNameRestDto
{
    public function __construct(
        public string $language,
        public string $name,
        public string $shortName,
    ) {
    }
}
