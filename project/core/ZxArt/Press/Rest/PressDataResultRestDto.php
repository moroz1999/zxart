<?php

declare(strict_types=1);

namespace ZxArt\Press\Rest;

final readonly class PressDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
