<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\Releases\Dto\ReleaseFieldsDto;

final readonly class UpdateReleaseCommand
{
    /**
     * @param ReleaseFieldsDto $fields only the fields it carries change
     */
    public function __construct(
        public int $releaseId,
        public ReleaseFieldsDto $fields,
    ) {
    }
}
