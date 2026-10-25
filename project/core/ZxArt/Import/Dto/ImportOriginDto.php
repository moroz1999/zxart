<?php

declare(strict_types=1);

namespace ZxArt\Import\Dto;

use ZxArt\Import\ImportOrigin;

/**
 * A portal an entity is known on, with the entity's id there.
 *
 * @psalm-api
 */
final readonly class ImportOriginDto
{
    public function __construct(
        public ImportOrigin $origin,
        public string $importId,
    ) {
    }
}
