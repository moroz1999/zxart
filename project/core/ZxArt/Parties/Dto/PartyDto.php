<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

readonly class PartyDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $year,
    ) {
    }
}
