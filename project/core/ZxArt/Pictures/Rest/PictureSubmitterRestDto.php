<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

readonly class PictureSubmitterRestDto
{
    public function __construct(
        public string $userName,
        public string $url,
    ) {
    }
}
