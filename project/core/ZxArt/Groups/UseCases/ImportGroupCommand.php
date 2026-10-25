<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;

final readonly class ImportGroupCommand
{
    public function __construct(
        public GroupLabel $label,
        public ImportOrigin $origin,
    ) {
    }
}
