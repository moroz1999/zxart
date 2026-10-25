<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Import\Authors\AuthorImportPolicy;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\PersonLabel;

final readonly class ImportAuthorCommand
{
    public function __construct(
        public PersonLabel $label,
        public ImportOrigin $origin,
        public AuthorImportPolicy $policy,
        /** false: an author the label does not match is not created */
        public bool $createNew = true,
    ) {
    }
}
