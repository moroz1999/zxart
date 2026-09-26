<?php

declare(strict_types=1);

namespace ZxArt\EntityConversion\Rest;

final readonly class ConvertedEntityRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
