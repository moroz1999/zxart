<?php

declare(strict_types=1);

namespace ZxArt\Import\Services;

use ZxArt\Import\Dbal\ImportOriginsDbal;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\EntityType;

/**
 * Makes the portal ids an element carries exactly the given list. The same
 * pair given twice counts once; a pair another element of the type held moves
 * here, since one portal id describes one element.
 *
 * @psalm-api
 */
final readonly class ImportOriginsWriter
{
    public function __construct(
        private ImportOriginsDbal $importOriginsDbal,
    ) {
    }

    /**
     * @param list<ImportOriginDto> $origins
     */
    public function replace(int $elementId, EntityType $type, array $origins): void
    {
        $listed = [];
        foreach ($origins as $origin) {
            $listed[$origin->origin->value . '|' . $origin->importId] = $origin;
        }
        foreach ($this->importOriginsDbal->getElementOrigins($elementId) as $row) {
            if (!isset($listed[$row['origin'] . '|' . $row['importId']])) {
                $this->importOriginsDbal->deleteOrigin($elementId, $row['origin'], $row['importId']);
            }
        }
        foreach ($listed as $origin) {
            $this->importOriginsDbal->saveOrigin($elementId, $origin->origin, $origin->importId, $type);
        }
    }
}
