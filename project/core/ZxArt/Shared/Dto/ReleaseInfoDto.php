<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

readonly class ReleaseInfoDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
