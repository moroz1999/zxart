<?php

declare(strict_types=1);

namespace ZxArt\Import\Services;

use ZxArt\Import\Dbal\ImportOriginsDbal;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;

/**
 * The portals an element is known on, with its id on each.
 *
 * @psalm-api
 */
final readonly class ImportOriginsReader
{
    public function __construct(
        private ImportOriginsDbal $importOriginsDbal,
    ) {
    }

    /**
     * @return ImportOriginDto[] by portal, then by id
     */
    public function read(int $elementId): array
    {
        $origins = [];
        foreach ($this->importOriginsDbal->getElementOrigins($elementId) as $row) {
            $origin = ImportOrigin::tryFrom($row['origin']);
            if ($origin !== null) {
                $origins[] = new ImportOriginDto($origin, $row['importId']);
            }
        }

        return $origins;
    }
}
