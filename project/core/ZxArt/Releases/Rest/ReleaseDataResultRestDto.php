<?php

declare(strict_types=1);

namespace ZxArt\Releases\Rest;

final readonly class ReleaseDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
