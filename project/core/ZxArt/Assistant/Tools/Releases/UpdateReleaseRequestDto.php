<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use ZxArt\Releases\Dto\ReleaseFieldsDto;

/**
 * @psalm-api
 */
final readonly class UpdateReleaseRequestDto
{
    /**
     * @param int $id the release
     * @param ReleaseFieldsDto $fields only the fields to change; a list given replaces the whole list, an empty value clears
     */
    public function __construct(
        public int $id,
        public ReleaseFieldsDto $fields,
    ) {
    }
}
