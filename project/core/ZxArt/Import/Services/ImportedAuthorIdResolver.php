<?php

declare(strict_types=1);

namespace ZxArt\Import\Services;

use RuntimeException;
use structureElement;
use structureManager;
use ZxArt\Import\ImportOrigin;
use ZxArt\Shared\EntityType;

/**
 * The author element an imported credit points at. Authorship is stored by
 * element id, so an import id pointing at an element the structure cannot load
 * would attach the credit to nothing and break the next import run the same
 * way: that author is named and the import stops.
 *
 * @psalm-api
 */
final readonly class ImportedAuthorIdResolver
{
    public function __construct(
        private ImportIdOperator $importIdOperator,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @return int|null null when the source's author was never imported
     * @throws RuntimeException for an author the structure cannot load
     */
    public function resolve(int|string $importAuthorId, ImportOrigin $origin, structureElement $element): ?int
    {
        $importId = (string)$importAuthorId;
        $authorId = $this->importIdOperator->getElementIdByImportId($importId, $origin, EntityType::Author);
        if ($authorId === null) {
            return null;
        }

        $authorElement = $this->structureManager->getElementById($authorId);
        if ($authorElement === null) {
            throw new RuntimeException(
                sprintf(
                    'Author element %d (%s import id %s) is not available in the structure and is likely half-created. Reached while importing %s %d "%s"',
                    $authorId,
                    $origin->value,
                    $importId,
                    $element->structureType,
                    $element->getId(),
                    $element->title
                )
            );
        }

        return $authorId;
    }
}
