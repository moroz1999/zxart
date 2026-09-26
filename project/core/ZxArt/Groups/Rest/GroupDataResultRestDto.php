<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

final readonly class GroupDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
