<?php

declare(strict_types=1);

namespace ZxArt\GroupList\Rest;

readonly class GroupFilterOptionRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
