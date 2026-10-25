<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;

/**
 * Brings what an import label says about an author alias into it, filling only
 * what is empty, and saves it when anything changed.
 *
 * @psalm-api
 */
final readonly class ImportedAuthorAliasMerger
{
    public function __construct(
        private ImportIdOperator $importIdOperator,
        private AuthorAliasRepository $authorAliasRepository,
    ) {
    }

    public function merge(authorAliasElement $element, PersonLabel $label, ImportOrigin $origin): void
    {
        $changed = false;

        $title = trim($label->name ?? '');
        if ($title !== '' && !$element->title) {
            $element->title = $title;
            $element->structureName = $title;
            $changed = true;
        }

        if ($label->authorId !== null && !$element->authorId) {
            if ($authorElement = $this->importIdOperator->getElementByImportId((string)$label->authorId, $origin, EntityType::AuthorAlias)) {
                if ($authorElement->getId() !== $element->authorId) {
                    $element->authorId = $authorElement->getId();
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $this->authorAliasRepository->save($element);
        }
    }
}
