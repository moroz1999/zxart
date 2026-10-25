<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Import\Labels;

use ZxArt\Authors\Services\AuthorImportService;
use ZxArt\Authors\Services\ImportedAuthorsFinder;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Groups\UseCases\ImportGroupCommand;
use ZxArt\Groups\UseCases\ImportGroupHandler;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;

/**
 * Imports the authors, aliases and groups a production or release names, so
 * that their import ids lead to elements before it links them. A label the
 * source tells nothing about is matched to a group, an alias or an author of
 * its name, or becomes a new author.
 */
final readonly class ImportedLabelsImporter
{
    public function __construct(
        private LabelTransformer $labelTransformer,
        private AuthorImportService $authorImportService,
        private ImportedAuthorsFinder $importedAuthorsFinder,
        private ImportGroupHandler $importGroupHandler,
        private GroupsService $groupsService,
        private ImportIdOperator $importIdOperator,
    ) {
    }

    /**
     * @param Label[] $labels
     */
    public function import(array $labels, ImportOrigin $origin): void
    {
        $labels = array_reverse($labels);
        foreach ($labels as $label) {
            $personLabel = $this->labelTransformer->toPersonLabel($label);
            $groupLabel = $this->labelTransformer->toGroupLabel($label);

            if ($label->isAlias && $label->isPerson) {
                $this->authorImportService->importAuthorAlias($personLabel, $origin);
            } elseif ($label->isGroup && $label->isAlias === false) {
                $this->importGroupHandler->handle(new ImportGroupCommand($groupLabel, $origin));
            } elseif ($label->isPerson && $label->isAlias === false) {
                $this->authorImportService->importAuthor($personLabel, $origin);
            } else {
                //we don't know anything about this label. lets search for any group with that name
                $element = $this->groupsService->getGroupByLabel($groupLabel, $origin);
                if ($element !== null) {
                    $this->importIdOperator->saveImportId($element->getId(), $label->id, $origin, EntityType::Group);
                    continue;
                }
                //search for author alias with that name
                $element = $this->importedAuthorsFinder->getAuthorAliasByLabel($personLabel, $origin);
                if ($element !== null) {
                    $this->importIdOperator->saveImportId($element->getId(), $label->id, $origin, EntityType::Author);
                    continue;
                }
                //search for author with that name
                $element = $this->importedAuthorsFinder->getAuthorByLabel($personLabel, $origin);
                if ($element !== null) {
                    $this->importIdOperator->saveImportId($element->getId(), $label->id, $origin, EntityType::Author);
                    continue;
                }

                //just create author by default.
                $this->authorImportService->importLabel($personLabel, $origin);
            }
        }
    }
}
