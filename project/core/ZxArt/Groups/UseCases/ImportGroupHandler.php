<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Groups\UseCases;

use groupAliasElement;
use groupElement;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Groups\Services\ImportedGroupAliasMerger;
use ZxArt\Groups\Services\ImportedGroupMerger;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Imports a group or a group alias from a label: the one it stands for, by
 * import id or else by name, gets what the label adds; with none, a new one is
 * created. One transaction: an import that stops halfway leaves nothing behind.
 *
 * @psalm-api
 */
final readonly class ImportGroupHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupsService $groupsService,
        private ImportIdOperator $importIdOperator,
        private ImportedGroupMerger $importedGroupMerger,
        private ImportedGroupAliasMerger $importedGroupAliasMerger,
    ) {
    }

    public function handle(ImportGroupCommand $command): groupAliasElement|groupElement|null
    {
        $label = $command->label;
        $origin = $command->origin;

        return $this->transactionRunner->run(function () use ($label, $origin) {
            $element = $this->groupsService->getGroupByLabel($label, $origin);
            if ($element !== null) {
                $this->importIdOperator->saveImportId($element->id, $label->id, $origin, EntityType::Group);
            }

            if ($element === null) {
                if ($label->isAlias) {
                    $element = $this->createGroupAlias($label, $origin);
                } else {
                    $element = $this->createGroup($label, $origin);
                }
            }

            if ($element instanceof groupAliasElement) {
                $this->importedGroupAliasMerger->merge($element, $label, $origin);
            } else {
                $this->importedGroupMerger->merge($element, $label, $origin);
            }

            return $element;
        });
    }

    private function createGroup(GroupLabel $dto, ImportOrigin $origin): ?groupElement
    {
        if ($element = $this->groupsService->manufactureGroupElement($dto->name ?? '')) {
            $this->importedGroupMerger->merge($element, $dto, $origin);
            if ($dto->id !== null) {
                $this->importIdOperator->saveImportId($element->id, (string)$dto->id, $origin, EntityType::Group);
            }
        }
        return $element ?? null;
    }

    private function createGroupAlias(GroupLabel $dto, ImportOrigin $origin): ?groupAliasElement
    {
        if ($element = $this->groupsService->manufactureAliasElement($dto->name ?? '')) {
            $this->importedGroupAliasMerger->merge($element, $dto, $origin);
            if ($dto->id !== null) {
                $this->importIdOperator->saveImportId($element->id, (string)$dto->id, $origin, EntityType::Group);
            }
        }
        return $element ?? null;
    }
}
