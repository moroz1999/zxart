<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

/**
 * A file in one of the file lists of a production or a release.
 *
 * @psalm-api
 */
final readonly class AttachedFileDto
{
    public function __construct(
        public int $id,
        public string $fileName,
    ) {
    }
}
