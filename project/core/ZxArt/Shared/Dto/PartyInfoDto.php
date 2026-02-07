<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

readonly class PartyInfoDto
{
    public function __construct(
        public string $title,
        public string $url,
        public ?int $place,
    ) {
    }
}
