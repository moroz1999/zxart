<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use ZxArt\Releases\Dto\ReleaseFieldsDto;

/**
 * @psalm-api
 */
final readonly class CreateReleaseRequestDto
{
    /**
     * @param ReleaseFieldsDto $fields the fields of the new release; title and prodId are required
     */
    public function __construct(
        public ReleaseFieldsDto $fields,
    ) {
    }
}
