<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Rest;

readonly class HardwareItemRestDto
{
    /**
     * @param array<string, HardwareNameRestDto> $names keyed by two-letter
     *        language code, so the management form can address a language directly
     */
    public function __construct(
        public int $id,
        public string $code,
        public string $category,
        public int $position,
        public array $names,
        public int $usages,
    ) {
    }
}
