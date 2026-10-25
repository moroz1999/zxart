<?php

declare(strict_types=1);

namespace ZxArt\Shared\Rest;

final readonly class CreatedIdsRestDto
{
    /**
     * @param list<int> $ids
     */
    public function __construct(
        public array $ids,
    ) {
    }
}
