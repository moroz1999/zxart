<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

/**
 * A file uploaded with a request: where it lies now, and the name it was sent
 * under. Storing it copies it and leaves it where it is.
 */
final readonly class UploadedFileDto
{
    public function __construct(
        public string $path,
        public string $fileName,
    ) {
    }
}
