<?php

namespace ZxArt\Groups\Services;

use authorElement;
use ConfigManager;
use CountriesManager;
use ElementsManager;
use groupAliasElement;
use groupElement;
use Illuminate\Database\Connection;
use ImportIdOperatorTrait;
use LanguagesManager;
use LettersElementsListProviderTrait;
use linksManager;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Labels\GroupLabel;

class GroupsService extends ElementsManager
{
    protected const TABLE = 'module_group';

    use LettersElementsListProviderTrait;
    use ImportIdOperatorTrait;

    protected array $columnRelations = [];
    protected array $importedGroups = [];

    public function __construct(
        protected LabelResolver        $labelResolver,
        protected linksManager         $linksManager,
        protected structureManager     $structureManager,
        protected CountriesManager     $countriesManager,
        protected ConfigManager        $configManager,
        protected privilegesManager    $privilegesManager,
        protected AuthorshipRepository $authorshipRepository,
        protected languagesManager     $languagesManager,
        protected Connection           $db,
    )
    {
        $this->columnRelations = [
            'title' => ['LOWER(title)' => true],
            'date' => ['id' => true],
        ];
    }

    public function importGroup($groupInfo, ?string $origin = null, bool $createNew = true, ?array $memberNames = null): GroupAliasElement|GroupElement|null
    {
        $label = new GroupLabel(
            id: $groupInfo['id'] ?? null,
            name: $groupInfo['title'],
            city: $groupInfo['cityName'] ?? null,
            country: $groupInfo['countryName'] ?? null,
            isAlias: $groupInfo['isAlias'] ?? null,
            memberNames: $memberNames,
            parentGroupIds: $groupInfo['parentGroupIds'] ?? null
        );

        /**
         * @var groupElement|groupAliasElement|null $element
         */
        $element = $this->getElementByImportId($label->id, $origin, 'group');
        if ($element === null) {
            $element = $this->labelResolver->resolve($label);

            if (($element !== null) && $origin !== null) {
                $this->saveImportId($element->id, $label->id, $origin, 'group');
            }
        }
        if ($createNew === false && $element === null) {
            return null;
        }

        if ($element === null) {
            if ($label->isAlias) {
                $element = $this->createGroupAlias($groupInfo, $origin);
            } else {
                $element = $this->createGroup($groupInfo, $origin);
            }
        }

        if ($element instanceof groupAliasElement) {
            $this->updateGroupAlias($element, $groupInfo, $origin);
        } else {
            $this->updateGroup($element, $groupInfo, $origin);
        }

        return $element;
    }

    protected function createGroup(array $groupInfo, $origin): ?groupElement
    {
        if ($element = $this->manufactureGroupElement($groupInfo['title'])) {
            $this->updateGroup($element, $groupInfo, $origin);
            $this->saveImportId($element->id, $groupInfo['id'], $origin, 'group');
        }
        return $element;
    }

    public function manufactureGroupElement(string $title): ?groupElement
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                if ($element = $this->structureManager->createElement('group', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return null;
    }

    protected function updateGroup(groupElement $element, array $groupInfo, $origin): void
    {
        $changed = false;
        if (!empty($groupInfo['title']) && $element->title !== $groupInfo['title'] && !$element->title) {
            $changed = true;
            $element->title = $groupInfo['title'];
            $element->structureName = $groupInfo['title'];
        }
        if (!empty($groupInfo['type']) && $element->type !== $groupInfo['type'] && !$element->type) {
            $changed = true;
            $element->type = $groupInfo['type'];
        }
        if (!empty($groupInfo['abbreviation']) && $element->abbreviation !== $groupInfo['abbreviation'] && !$element->abbreviation) {
            $changed = true;
            $element->abbreviation = $groupInfo['abbreviation'];
        }
        if (!empty($groupInfo['website']) && $element->website !== $groupInfo['website'] && !$element->website) {
            $changed = true;
            $element->website = $groupInfo['website'];
        }
        if (!empty($groupInfo['type']) && $element->type !== $groupInfo['type']) {
            if (!$element->type || $element->type === 'unknown') {
                $changed = true;
                $element->type = $groupInfo['type'];
            }
        }
        if (!empty($groupInfo['locationName'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($groupInfo['locationName'])) {
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
        if (!empty($groupInfo['countryName'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($groupInfo['countryName'])) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country != $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                }
            }
        }
        if (!empty($groupInfo['cityName'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($groupInfo['cityName'])) {
                if ($locationElement->structureType === 'city') {
                    if ($element->city != $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                    }
                }
            }
        }
        if (!empty($groupInfo['countryId'])) {
            $locationElement = $this->getElementByImportId($groupInfo['countryId'], $origin, 'country');
            if ($locationElement && $element->country != $locationElement->id) {
                $changed = true;
                $element->country = $locationElement->id;
            }
        }

        if (isset($groupInfo['parentGroupIds'])) {
            $parentGroups = $element->parentGroups;
            foreach ($groupInfo['parentGroupIds'] as $groupId) {
                $groupElement = $this->getElementByImportId($groupId, $origin, 'group');
                if ($groupElement !== null && !in_array($groupElement, $parentGroups, true)) {
                    $parentGroups[] = $groupElement;
                }
            }
            $changed = true;
            $element->parentGroups = $parentGroups;
        }


        if ($changed) {
            $element->persistElementData();
        }
    }

    protected function createGroupAlias(array $groupAliasInfo, $origin): ?groupAliasElement
    {
        if ($element = $this->manufactureAliasElement($groupAliasInfo['title'])) {
            $this->updateGroupAlias($element, $groupAliasInfo, $origin);
            $this->saveImportId($element->id, $groupAliasInfo['id'], $origin, 'group');
        }

        return $element;
    }

    protected function manufactureAliasElement(string $title = ''): ?groupAliasElement
    {
        if (($letterId = $this->getLetterId($title)) &&
            ($letterElement = $this->structureManager->getElementById($letterId)) &&
            ($element = $this->structureManager->createElement('groupAlias', 'show', $letterElement->id))
        ) {
            return $element;
        }
        return null;
    }

    protected function updateGroupAlias(groupAliasElement $element, array $groupAliasInfo, $origin): void
    {
        $changed = false;
        if (!empty($groupAliasInfo['title']) && $element->title != $groupAliasInfo['title']) {
            if (!$element->title) {
                $changed = true;
                $element->title = $groupAliasInfo['title'];
                $element->structureName = $groupAliasInfo['title'];
            }
        }
        if (!empty($groupAliasInfo['groupId']) && !$element->groupId) {
            if ($groupElement = $this->getElementByImportId($groupAliasInfo['groupId'], $origin, 'group')) {
                $changed = true;
                $element->groupId = $groupElement->getId();
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

                foreach ($authorElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($groupElement->id, $zxProd->id, 'zxProdPublishers');
                }
                foreach ($authorElement->getAuthorshipRecords('prod') as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxProdGroups');
                    }
                }
                foreach ($authorElement->getAuthorshipRecords('release') as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxReleasePublishers');
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


    /**
     * @param groupAliasElement $groupAliasElement
     * @return bool|groupElement
     */
    public function convertGroupAliasToGroup($groupAliasElement)
    {
        $groupElement = false;
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
//                foreach ($groupAliasElement->publishedReleases as $zxRelease) {
//                    $this->checkAuthorship($zxRelease->id, $authorElement->id, 'release', ['release']);
//                }


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