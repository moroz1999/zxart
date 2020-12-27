<?php

class GroupsManager extends ElementsManager
{
    const TABLE = 'module_group';

    use LettersElementsListProviderTrait;
    use ImportIdOperatorTrait;

    protected $columnRelations = [];
    protected $importedGroups = [];
    protected $importedGroupAliases = [];
    /**
     * @var linksManager
     */
    protected $linksManager;
    /**
     * @var CountriesManager
     */
    protected $countriesManager;
    /**
     * @var languagesManager
     */
    protected $languagesManager;
    /**
     * @var configManager
     */
    protected $configManager;
    /**
     * @var privilegesManager
     */
    protected $privilegesManager;

    /**
     * @var AuthorsManager
     */
    protected $authorsManager;

    public function __construct()
    {
        $this->columnRelations = [
            'title' => ['LOWER(title)' => true],
            'date' => ['id' => true],
        ];
    }

    /**
     * @param AuthorsManager $authorsManager
     */
    public function setAuthorsManager($authorsManager)
    {
        $this->authorsManager = $authorsManager;
    }

    /**
     * @param privilegesManager $privilegesManager
     */
    public function setPrivilegesManager($privilegesManager)
    {
        $this->privilegesManager = $privilegesManager;
    }

    /**
     * @param languagesManager $languagesManager
     */
    public function setLanguagesManager($languagesManager)
    {
        $this->languagesManager = $languagesManager;
    }

    /**
     * @param linksManager $linksManager
     */
    public function setLinksManager($linksManager)
    {
        $this->linksManager = $linksManager;
    }

    /**
     * @param CountriesManager $countriesManager
     */
    public function setCountriesManager($countriesManager)
    {
        $this->countriesManager = $countriesManager;
    }


    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
    }

    public function getGroupByName($groupName)
    {
        $groupElement = false;
        $structureManager = $this->structureManager;

        if ($record = $this->db->table('module_group')
            ->select('id')
            ->where('title', 'like', $groupName)
            ->limit(1)
            ->first()
        ) {
            /**
             * @var groupElement $groupElement
             */
            if ($groupElement = $structureManager->getElementById($record['id'])) {
                return $groupElement;
            };
        }

        return $groupElement;
    }

    public function getGroupAliasByName($groupAliasName)
    {
        $groupAliasElement = false;
        $structureManager = $this->structureManager;

        if ($record = $this->db->table('module_groupalias')
            ->select('id')
            ->where('title', 'like', $groupAliasName)
            ->limit(1)
            ->first()
        ) {
            /**
             * @var groupAliasElement $groupAliasElement
             */
            if ($groupAliasElement = $structureManager->getElementById($record['id'])) {
                return $groupAliasElement;
            };
        }

        return $groupAliasElement;
    }

    public function importGroup($groupInfo, $origin, $createNew = true)
    {
        if (!isset($this->importedGroups[$origin][$groupInfo['id']])) {
            /**
             * @var groupElement $element
             */
            if (!($element = $this->getElementByImportId($groupInfo['id'], $origin, 'group'))) {
                if ($element = $this->getGroupByName($groupInfo['title'])) {
                    $this->saveImportId($element->id, $groupInfo['id'], $origin, 'group');
                    $this->updateGroup($element, $groupInfo, $origin);
                } elseif ($createNew) {
                    $element = $this->createGroup($groupInfo, $origin);
                }
            } else {
                $this->updateGroup($element, $groupInfo, $origin);
            }
            $this->importedGroups[$origin][$groupInfo['id']] = $element;
        }
        return $this->importedGroups[$origin][$groupInfo['id']];
    }

    /**
     * @param array $groupInfo
     * @param $origin
     * @return bool|groupElement
     */
    protected function createGroup($groupInfo, $origin)
    {
        if ($element = $this->manufactureGroupElement($groupInfo['title'])) {
            $this->updateGroup($element, $groupInfo, $origin);
            $this->saveImportId($element->id, $groupInfo['id'], $origin, 'group');
        }
        return $element;
    }

    /**
     * @param $title
     * @return bool|groupElement
     */
    public function manufactureGroupElement($title)
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                if ($element = $this->structureManager->createElement('group', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return false;
    }

    /**
     * @param groupElement $element
     * @param $groupInfo
     * @param $origin
     */
    protected function updateGroup($element, $groupInfo, $origin)
    {
        $changed = false;
        if (!empty($groupInfo['title']) && $element->title != $groupInfo['title']) {
            if (!$element->title) {
                $changed = true;
                $element->title = $groupInfo['title'];
                $element->structureName = $groupInfo['title'];
            }
        }
        if (!empty($groupInfo['abbreviation']) && $element->abbreviation != $groupInfo['abbreviation']) {
            if (!$element->abbreviation) {
                $changed = true;
                $element->abbreviation = $groupInfo['abbreviation'];
            }
        }
        if (!empty($groupInfo['website']) && $element->website != $groupInfo['website']) {
            if (!$element->website) {
                $changed = true;
                $element->website = $groupInfo['website'];
            }
        }
        if (!empty($groupInfo['locationLabel'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($groupInfo['locationLabel'])) {
                if ($locationElement->structureType == 'country') {
                    if ($element->country != $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                } elseif ($locationElement->structureType == 'city') {
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
        if (!empty($groupInfo['countryId'])) {
            $locationElement = $this->getElementByImportId($groupInfo['countryId'], $origin, 'country');
            if ($locationElement && $element->country != $locationElement->id) {
                $changed = true;
                $element->country = $locationElement->id;
            }
        }
        if ($changed) {
            $element->persistElementData();
        }
    }

    public function importGroupAlias($groupAliasInfo, $origin, $createNew = true)
    {
        if (!isset($this->importedGroupAliases[$origin][$groupAliasInfo['id']])) {
            /**
             * @var groupAliasElement $element
             */
            if (!($element = $this->getElementByImportId($groupAliasInfo['id'], $origin, 'group'))) {
                if ($element = $this->getGroupAliasByName($groupAliasInfo['title'])) {
                    $this->saveImportId($element->id, $groupAliasInfo['id'], $origin, 'group');
                    $this->updateGroupAlias($element, $groupAliasInfo, $origin);
                } elseif ($createNew) {
                    $element = $this->createGroupAlias($groupAliasInfo, $origin);
                }
            } else {
                $this->updateGroupAlias($element, $groupAliasInfo, $origin);
            }

            $this->importedGroupAliases[$origin][$groupAliasInfo['id']] = $element;
        }
        return $this->importedGroupAliases[$origin][$groupAliasInfo['id']];
    }

    /**
     * @param array $groupAliasInfo
     * @param $origin
     * @return bool|groupAliasElement
     */
    protected function createGroupAlias($groupAliasInfo, $origin)
    {
        if ($element = $this->manufactureAliasElement($groupAliasInfo['title'])) {
            $this->updateGroupAlias($element, $groupAliasInfo, $origin);
            $this->saveImportId($element->id, $groupAliasInfo['id'], $origin, 'group');
        }

        return $element;
    }

    /**
     * @param string $title
     * @return groupAliasElement|bool
     */
    protected function manufactureAliasElement($title = '')
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                if ($element = $this->structureManager->createElement('groupAlias', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return false;
    }

    /**
     * @param groupAliasElement $element
     * @param $groupAliasInfo
     * @param $origin
     */
    protected function updateGroupAlias($element, $groupAliasInfo, $origin)
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

    /**
     * @param authorElement $authorElement
     * @return bool|groupElement
     */
    public function convertAuthorToGroup($authorElement)
    {
        $groupElement = false;
        if ($authorElement->structureType == 'author') {
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
        if ($groupAliasElement->structureType == 'groupAlias') {
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

    protected function getLettersListMarker($type)
    {
        if ($type == 'admin') {
            return 'groups';
        } else {
            return 'groupsmenu';
        }
    }

    public function joinDeleteGroup($mainGroupId, $joinedGroupId)
    {
        $this->joinGroup($mainGroupId, $joinedGroupId, false);
    }

    public function joinGroupAsAlias($mainGroupId, $joinedGroupId)
    {
        $this->joinGroup($mainGroupId, $joinedGroupId, true);
    }

    protected function joinGroup($targetId, $joinedId, $makeAlias = true)
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
                if ($targetElement->structureType == 'groupAlias') {
                    $targetGroupElement = $targetElement->getGroupElement();
                } elseif ($targetElement->structureType == 'group') {
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
                if ($joinedElement->structureType == 'group') {
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
                if ($existingGroupShipRecords = $this->authorsManager->getAuthorsInfo($targetElement->id, 'group')) {
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