<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\Releases\Dto\ReleaseFieldsDto;

final readonly class CreateReleaseCommand
{
    public function __construct(
        public ReleaseFieldsDto $fields,
    ) {
    }
}
