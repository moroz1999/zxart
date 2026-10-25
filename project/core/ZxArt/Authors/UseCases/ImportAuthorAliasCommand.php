<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\PersonLabel;

final readonly class ImportAuthorAliasCommand
{
    public function __construct(
        public PersonLabel $label,
        public ImportOrigin $origin,
        /** false: an alias the label does not match is not created */
        public bool $createNew = true,
    ) {
    }
}
