<?php

declare(strict_types=1);

namespace ZxArt\Search\Rest;

final readonly class EntityLookupItemRestDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
