<?php

declare(strict_types=1);

namespace ZxArt\Menu\Rest;

readonly class MenuRestItemDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        /** @var MenuRestItemDto[] */
        public array $children = [],
    ) {}
}
