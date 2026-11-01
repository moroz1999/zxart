<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

final readonly class RunRestDto
{
    public function __construct(
        public int $id,
        public string $type,
        public string $status,
        public int $steps,
        public ?string $error,
        public int $startedAt,
        public bool $stopRequested,
    ) {
    }
}
