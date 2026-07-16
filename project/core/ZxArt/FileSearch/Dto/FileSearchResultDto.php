<?php

declare(strict_types=1);

namespace ZxArt\FileSearch\Dto;

readonly class FileSearchResultDto
{
    public function __construct(
        public string $fileName,
        public string $md5,
        public string $title,
        public string $url,
        public string $type,
    ) {
    }
}
