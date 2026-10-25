<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

/**
 * The element files were added to, and the new file elements.
 *
 * @psalm-api
 */
final readonly class AddedFilesDto
{
    /**
     * @param int[] $fileIds in the order of the URLs
     */
    public function __construct(
        public int $id,
        public array $fileIds,
    ) {
    }
}
