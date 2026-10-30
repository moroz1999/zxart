<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

/**
 * A file of one of an element's file lists.
 *
 * @psalm-api
 */
final readonly class ListedFileDto
{
    public function __construct(
        /** the list: screenshots, inlays, maps, rzx, info, ads */
        public string $kind,
        public int $id,
        public string $fileName,
    ) {
    }
}
