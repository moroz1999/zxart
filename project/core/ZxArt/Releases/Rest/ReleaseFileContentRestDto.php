<?php

declare(strict_types=1);

namespace ZxArt\Releases\Rest;

readonly class ReleaseFileContentRestDto
{
    public function __construct(
        public int $id,
        public string $fileName,
        public int $size,
        public string $md5,
        public ?string $contentHtml,
    ) {
    }
}
