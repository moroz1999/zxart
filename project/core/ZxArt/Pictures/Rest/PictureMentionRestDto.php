<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

readonly class PictureMentionRestDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
