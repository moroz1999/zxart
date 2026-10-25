<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Groups\Services;

use groupAliasElement;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Shared\EntityType;

/**
 * Brings what an import label says about a group alias into it, filling only
 * what is empty, and saves it when anything changed.
 *
 * @psalm-api
 */
final readonly class ImportedGroupAliasMerger
{
    public function __construct(
        private ImportIdOperator $importIdOperator,
        private GroupAliasRepository $groupAliasRepository,
    ) {
    }

    public function merge(groupAliasElement $element, GroupLabel $groupAlias, ImportOrigin $origin): void
    {
        $changed = false;

        if (($groupAlias->name !== null && $groupAlias->name !== '') && $element->title !== $groupAlias->name) {
            if (!$element->title) {
                $changed = true;
                $element->title = $groupAlias->name;
                $element->structureName = $groupAlias->name;
            }
        }

        if (($groupAlias->aliasParentGroupId !== null) && !$element->groupId) {
            if ($groupElement = $this->importIdOperator->getElementByImportId($groupAlias->aliasParentGroupId, $origin, EntityType::Group)) {
                $changed = true;
                $element->groupId = $groupElement->getPersistedId();
            }
        }
        if ($changed) {
            $this->groupAliasRepository->save($element);
        }
    }
}
