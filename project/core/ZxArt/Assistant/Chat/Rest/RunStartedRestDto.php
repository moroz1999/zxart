<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

final readonly class RunStartedRestDto
{
    public function __construct(
        public int $runId,
    ) {
    }
}
