<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Releases\Rest\ReleaseFileContentRestDto;

#[Map(target: ReleaseFileContentRestDto::class)]
readonly class ReleaseFileContentDto
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
