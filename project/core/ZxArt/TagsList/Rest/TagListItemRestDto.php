<?php

declare(strict_types=1);

namespace ZxArt\TagsList\Rest;

readonly class TagListItemRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $amount,
    ) {
    }
}
