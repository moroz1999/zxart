<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

final readonly class TunePlayRequestDto
{
    public function __construct(
        public int $tuneId,
        public ?string $context = null,
    ) {
    }
}
