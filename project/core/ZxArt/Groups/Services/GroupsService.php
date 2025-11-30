<?php

namespace ZxArt\Groups\Services;

use authorElement;
use ConfigManager;
use CountriesManager;
use ElementsManager;
use groupAliasElement;
use groupElement;
use Illuminate\Database\Connection;
use LanguagesManager;
use LettersElementsListProviderTrait;
use linksManager;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\LinkTypes;

class GroupsService extends ElementsManager
{
    protected const string TABLE = 'module_group';

    use LettersElementsListProviderTrait;

    protected array $columnRelations = [];

    public function __construct(
        protected LabelResolver           $labelResolver,
        protected linksManager            $linksManager,
        protected structureManager        $structureManager,
        protected CountriesManager        $countriesManager,
        protected ConfigManager           $configManager,
        protected privilegesManager       $privilegesManager,
        protected AuthorshipRepository    $authorshipRepository,
        protected languagesManager        $languagesManager,
        protected Connection              $db,
        private readonly ImportIdOperator $importIdOperator,

    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
            'date' => ['id' => true],
        ];
    }


    public function importGroup(GroupLabel $label, ?string $origin = null): groupAliasElement|groupElement|null
    {
        $element = $this->getGroupByLabel($label, $origin);
        if (($element !== null) && $origin !== null) {
            $this->importIdOperator->saveImportId($element->id, $label->id, $origin, 'group');
        }

        if ($element === null) {
            if ($label->isAlias) {
                $element = $this->createGroupAlias($label, $origin);
            } else {
                $element = $this->createGroup($label, $origin);
            }
        }

        if ($element instanceof groupAliasElement) {
            $this->updateGroupAlias($element, $label, $origin);
        } else {
            $this->updateGroup($element, $label, $origin);
        }

        return $element;
    }

    private function createGroup(GroupLabel $dto, string $origin): ?groupElement
    {
        if ($element = $this->manufactureGroupElement($dto->name ?? '')) {
            $this->updateGroup($element, $dto, $origin);
            if ($dto->id !== null) {
                $this->importIdOperator->saveImportId($element->id, (string)$dto->id, $origin, 'group');
            }
        }
        return $element ?? null;
    }

    private function manufactureGroupElement(string $title): ?groupElement
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                /**
                 * @var groupElement $element
                 */
                if ($element = $this->structureManager->createElement('group', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return null;
    }

    private function updateGroup(groupElement $element, GroupLabel $dto, string $origin): void
    {
        $changed = false;

        if (($dto->name !== '' && $dto->name !== null) && $element->title !== $dto->name && !$element->title) {
            $changed = true;
            $element->title = $dto->name;
            $element->structureName = $dto->name;
        }

        if (($dto->type !== '' && $dto->type !== null) && $element->type !== $dto->type) {
            if (!$element->type || $element->type === 'unknown') {
                $changed = true;
                $element->type = $dto->type;
            }
        }

        if (($dto->abbreviation !== '' && $dto->abbreviation !== null) && $element->abbreviation !== $dto->abbreviation && !$element->abbreviation) {
            $changed = true;
            $element->abbreviation = $dto->abbreviation;
        }

        if (($dto->website !== '' && $dto->website !== null) && $element->website !== $dto->website && !$element->website) {
            $changed = true;
            $element->website = $dto->website;
        }

        if ($dto->locationName !== '' && $dto->locationName !== null) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->locationName)) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country != $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                } elseif ($locationElement->structureType === 'city') {
                    if ($element->city != $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                        if ($countryId = $locationElement->getCountryId()) {
                            $element->country = $countryId;
                        }
                    }
                }
            }
        }

        if ($dto->countryName !== '' && $dto->countryName !== null) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->countryName)) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country !== $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                }
            }
        }

        if ($dto->cityName !== '' && $dto->cityName !== null) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->cityName)) {
                if ($locationElement->structureType === 'city') {
                    if ($element->city !== $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                    }
                }
            }
        }

        if ($dto->countryId !== 0 && $dto->countryId !== null) {
            $locationElement = $this->importIdOperator->getElementByImportId((string)$dto->countryId, $origin, 'country');
            if ($locationElement && $element->country !== $locationElement->id) {
                $changed = true;
                $element->country = $locationElement->id;
            }
        }

        if (is_array($dto->parentGroupIds)) {
            $parentGroups = $element->parentGroups;
            foreach ($dto->parentGroupIds as $groupId) {
                $groupElement = $this->importIdOperator->getElementByImportId((string)$groupId, $origin, 'group');
                if ($groupElement !== null && !in_array($groupElement, $parentGroups, true)) {
                    $parentGroups[] = $groupElement;
                }
            }
            $changed = true;
            $element->parentGroups = $parentGroups;
        }


        if ($changed) {
            $element->checkCountry();
            $element->persistElementData();
        }
    }

    private function createGroupAlias(GroupLabel $dto, string $origin): ?groupAliasElement
    {
        if ($element = $this->manufactureAliasElement($dto->name ?? '')) {
            $this->updateGroupAlias($element, $dto, $origin);
            if ($dto->id !== null) {
                $this->importIdOperator->saveImportId($element->id, (string)$dto->id, $origin, 'group');
            }
        }
        return $element ?? null;
    }

    private function manufactureAliasElement(string $title = ''): ?groupAliasElement
    {
        /**
         * @var groupAliasElement $element
         */
        if (($letterId = $this->getLetterId($title)) &&
            ($letterElement = $this->structureManager->getElementById($letterId)) &&
            ($element = $this->structureManager->createElement('groupAlias', 'show', $letterElement->id))
        ) {
            return $element;
        }
        return null;
    }

    private function updateGroupAlias(groupAliasElement $element, GroupLabel $groupAlias, string $origin): void
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
            if ($groupElement = $this->importIdOperator->getElementByImportId($groupAlias->aliasParentGroupId, $origin, 'group')) {
                $changed = true;
                $element->groupId = $groupElement->getPersistedId();
            }
        }
        if ($changed) {
            $element->persistElementData();
        }
    }

    public function convertAuthorToGroup(authorElement $authorElement): ?groupElement
    {
        $groupElement = null;
        if ($authorElement->structureType === 'author') {
            if ($groupElement = $this->manufactureGroupElement($authorElement->title)) {
                $groupElement->title = $authorElement->title;
                $groupElement->structureName = $authorElement->title;
                $groupElement->country = $authorElement->country;
                $groupElement->city = $authorElement->city;
                $groupElement->persistElementData();

                foreach ($authorElement->mentions as $article) {
                    $this->linksManager->linkElements($groupElement->id, $article->id, LinkTypes::PRESS_GROUPS->value);
                    $this->structureManager->clearElementCache($article->id);
                }
                foreach ($authorElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($groupElement->id, $zxProd->id, 'zxProdPublishers');
                    $this->structureManager->clearElementCache($zxProd->id);
                }
                foreach ($authorElement->getAuthorshipRecords('prod') as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getPersistedId(), 'zxProdGroups');
                        $this->structureManager->clearElementCache($groupElement->id);
                    }
                }
                foreach ($authorElement->getAuthorshipRecords('release') as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getPersistedId(), 'zxReleasePublishers');
                        $this->structureManager->clearElementCache($groupElement->id);
                    }
                }
                if ($records = $this->db->table('import_origin')
                    ->where('elementId', '=', $authorElement->id)
                    ->get()) {
                    foreach ($records as $record) {
                        if (!$this->db->table('import_origin')
                            ->where('type', '=', 'group')
                            ->where('importOrigin', '=', $record['importOrigin'])
                            ->where('importId', '=', $record['importId'])
                            ->limit(1)
                            ->get()) {
                            $this->db->table('import_origin')
                                ->where('elementId', '=', $authorElement->id)
                                ->update(
                                    [
                                        'elementId' => $groupElement->id,
                                        'type' => 'group',
                                    ]
                                );
                        } else {
                            $this->db->table('import_origin')
                                ->where('id', '=', $record['id'])->delete();
                        }
                    }
                }


                $authorElement->deleteElementData();
            }
        }
        return $groupElement;
    }

    public function convertGroupAliasToGroup(groupAliasElement $groupAliasElement): ?groupElement
    {
        $groupElement = null;
        if ($groupAliasElement->structureType === 'groupAlias') {
            if ($groupElement = $this->manufactureGroupElement($groupAliasElement->title)) {
                $groupElement->title = $groupAliasElement->title;
                $groupElement->structureName = $groupAliasElement->title;
                $groupElement->country = $groupAliasElement->country;
                $groupElement->city = $groupAliasElement->city;
                $groupElement->persistElementData();

                foreach ($groupAliasElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxProdPublishers');
                }
                foreach ($groupAliasElement->getGroupProds() as $zxProd) {
                    $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxProdGroups');
                }

                $this->db->table('authorship')
                    ->where('elementId', '=', $groupAliasElement->id)
                    ->update(['elementId' => $groupElement->id]);

                $this->db->table('import_origin')
                    ->where('elementId', '=', $groupAliasElement->id)
                    ->update(['elementId' => $groupElement->id]);


                $groupAliasElement->deleteElementData();
            }
        }
        return $groupElement;
    }

    public function getGroupByLabel(GroupLabel $label, ?string $origin): groupElement|groupAliasElement|null
    {
        /** @var groupElement|null $element */
        $element = $this->importIdOperator->getElementByImportId($label->id, $origin, 'group');
        if ($element === null) {
            $element = $this->labelResolver->resolveGroup($label);
        }
        return $element;
    }

    protected function getLettersListMarker(string $type): string
    {
        if ($type === 'admin') {
            return 'groups';
        }

        return 'groupsmenu';
    }

    public function joinDeleteGroup(int $mainGroupId, int $joinedGroupId): void
    {
        $this->joinGroup($mainGroupId, $joinedGroupId, false);
    }

    public function joinGroupAsAlias(int $mainGroupId, int $joinedGroupId): void
    {
        $this->joinGroup($mainGroupId, $joinedGroupId, true);
    }

    protected function joinGroup($targetId, $joinedId, bool $makeAlias = true): bool
    {
        if ($joinedId == $targetId) {
            return false;
        }
        /**
         * @var groupAliasElement|groupElement $joinedElement
         */
        if ($joinedElement = $this->structureManager->getElementById($joinedId)) {
            /**
             * @var groupElement $targetGroupElement
             */
            $targetGroupElement = false;
            /**
             * @var groupAliasElement|groupElement $targetElement
             */
            if ($targetElement = $this->structureManager->getElementById($targetId)) {
                if ($targetElement->structureType === 'groupAlias') {
                    $targetGroupElement = $targetElement->getGroupElement();
                } elseif ($targetElement->structureType === 'group') {
                    $targetGroupElement = $targetElement;
                }
                if ($makeAlias) {
                    $targetElement = $this->manufactureAliasElement($joinedElement->title);
                    $targetElement->title = $joinedElement->title;
                    $targetElement->structureName = $joinedElement->title;
                    $targetElement->groupId = $targetGroupElement->id;
                }
            }
            if ($targetElement && $targetGroupElement) {
                if ($joinedElement->structureType === 'group') {
                    if (!$targetGroupElement->country) {
                        $targetGroupElement->country = $joinedElement->country;
                    }
                    if (!$targetGroupElement->city) {
                        $targetGroupElement->city = $joinedElement->city;
                    }
                    if (!$targetGroupElement->website) {
                        $targetGroupElement->website = $joinedElement->website;
                    }
                    if (!$targetGroupElement->wikiLink) {
                        $targetGroupElement->wikiLink = $joinedElement->wikiLink;
                    }
                    if (!$targetGroupElement->startDate) {
                        $targetGroupElement->startDate = $joinedElement->startDate;
                    }
                    if (!$targetGroupElement->endDate) {
                        $targetGroupElement->endDate = $joinedElement->endDate;
                    }
                    if (!$targetGroupElement->slogan) {
                        $targetGroupElement->slogan = $joinedElement->slogan;
                    }
                    if ($joinedElement->type !== 'unknown' && $targetGroupElement->type === 'unknown') {
                        $targetGroupElement->type = $joinedElement->type;
                    }
                    $targetGroupElement->persistElementData();
                }
                $targetElement->persistElementData();

                if ($aliasElements = $joinedElement->getAliasElements()) {
                    /**
                     * @var groupAliasElement $aliasElement
                     */
                    foreach ($aliasElements as $aliasElement) {
                        $aliasElement->groupId = $targetGroupElement->id;
                        $aliasElement->persistElementData();
                    }
                }

                $this->privilegesManager->copyPrivileges($joinedElement->id, $targetGroupElement->id);

                if ($links = $this->linksManager->getElementsLinks($joinedId, null, 'parent')) {
                    foreach ($links as $link) {
                        $this->linksManager->unLinkElements($joinedId, $link->childStructureId, $link->type);
                        $this->linksManager->linkElements($targetElement->id, $link->childStructureId, $link->type);
                    }
                }


                //disabled groupship moving to new group
                //check if group already has groupship in same elements. we dont need duplicates
                $existingAuthorIds = [];
                if ($existingGroupShipRecords = $this->authorshipRepository->getAuthorsInfo($targetElement->id, 'group')) {
                    foreach ($existingGroupShipRecords as $record) {
                        $existingAuthorIds[] = $record['authorId'];
                    }
                }

                //delete duplicates from joined group
                if ($existingAuthorIds) {
                    $this->db->table('authorship')
                        ->where('elementId', '=', $joinedId)
                        ->whereIn('authorId', $existingAuthorIds)
                        ->delete();
                }

                //now move all remaining records to main group
                $this->db->table('authorship')
                    ->where('elementId', '=', $joinedId)
                    ->update(['elementId' => $targetElement->id]);

                $this->db->table('import_origin')
                    ->where('elementId', '=', $joinedId)
                    ->update(['elementId' => $targetElement->id]);

                $joinedElement->deleteElementData();
            }
        }
        return true;
    }

}