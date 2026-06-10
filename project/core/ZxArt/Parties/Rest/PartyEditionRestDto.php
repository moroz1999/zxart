<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

readonly class PartyEditionRestDto
{
    public function __construct(
        public int $id,
        public string $year,
        public string $url,
        public bool $current,
    ) {
    }
}
