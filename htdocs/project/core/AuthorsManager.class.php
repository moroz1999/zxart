<?php

class AuthorsManager extends ElementsManager
{
    use ImportIdOperatorTrait;
    use LettersElementsListProviderTrait;

    const TABLE = 'module_author';
    protected $forceUpdateCountry = false;
    protected $forceUpdateCity = false;
    protected $forceUpdateGroups = false;
    protected $columnRelations = [];
    protected $importedAuthors = [];
    protected $importedAuthorAliases = [];
    /**
     * @var linksManager
     */
    protected $linksManager;
    /**
     * @var ConfigManager
     */
    protected $configManager;
    /**
     * @var CountriesManager
     */
    protected $countriesManager;
    /**
     * @var privilegesManager
     */
    protected $privilegesManager;

    public function __construct()
    {
        $this->columnRelations = [
            'title' => ['LOWER(title)' => true],
            'date' => ['id' => true],
            'graphicsRating' => ['graphicsRating' => true, 'title' => false],
            'musicRating' => ['musicRating' => true, 'title' => false],
        ];
    }

    /**
     * @param bool $forceUpdateGroups
     */
    public function setForceUpdateGroups(bool $forceUpdateGroups): void
    {
        $this->forceUpdateGroups = $forceUpdateGroups;
    }

    /**
     * @param bool $forceUpdateCountry
     */
    public function setForceUpdateCountry(bool $forceUpdateCountry): void
    {
        $this->forceUpdateCountry = $forceUpdateCountry;
    }

    /**
     * @param bool $forceUpdateCity
     */
    public function setForceUpdateCity(bool $forceUpdateCity): void
    {
        $this->forceUpdateCity = $forceUpdateCity;
    }

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param CountriesManager $countriesManager
     */
    public function setCountriesManager($countriesManager)
    {
        $this->countriesManager = $countriesManager;
    }

    /**
     * @param privilegesManager $privilegesManager
     */
    public function setPrivilegesManager($privilegesManager)
    {
        $this->privilegesManager = $privilegesManager;
    }

    /**
     * @param linksManager $linksManager
     */
    public function setLinksManager($linksManager)
    {
        $this->linksManager = $linksManager;
    }

    public function getAuthorByName($authorName)
    {
        $authorElement = false;

        if ($record = $this->db->table('module_author')
            ->select('id')
            ->where('realName', 'like', $authorName)
            ->orWhere('title', 'like', $authorName)
            ->orWhere('title', 'like', htmlentities($authorName, ENT_QUOTES))
            ->limit(1)
            ->first()
        ) {
            /**
             * @var authorElement $authorElement
             */
            if ($authorElement = $this->structureManager->getElementById($record['id'])) {
                return $authorElement;
            };
        }

        return $authorElement;
    }

    public function getAuthorAliasByName($authorName)
    {
        $authorAliasElement = false;

        if ($record = $this->db->table('module_authoralias')
            ->select('id')
            ->orWhere('title', 'like', $authorName)
            ->orWhere('title', 'like', htmlentities($authorName, ENT_QUOTES))
            ->limit(1)
            ->first()
        ) {
            /**
             * @var authorAliasElement $authorAliasElement
             */
            if ($authorAliasElement = $this->structureManager->getElementById($record['id'])) {
                return $authorAliasElement;
            };
        }

        return $authorAliasElement;
    }

    public function getAuthorsInfo($elementId, $type)
    {
        $info = [];
        if ($records = $this->getElementAuthorsRecords($elementId, $type)
        ) {
            foreach ($records as $key => $record) {
                if ($authorElement = $this->structureManager->getElementById($record['authorId'])) {
                    $record['authorElement'] = $authorElement;
                    $info[] = $record;
                }
            }
        }
        return $info;
    }

    public function getElementAuthorsRecords($elementId, $type = null)
    {
        $query = $this->db
            ->table('authorship')
            ->select('id', 'authorId', 'startDate', 'endDate', 'roles')
            ->where('elementId', '=', $elementId);
        if ($type) {
            $query->where('type', '=', $type);
        }

        if ($records = $query->get()) {
            foreach ($records as $key => &$record) {
                if ($record['startDate']) {
                    $record['startDate'] = date('d.m.Y', $record['startDate']);
                } else {
                    $record['startDate'] = '';
                }
                if ($record['endDate']) {
                    $record['endDate'] = date('d.m.Y', $record['endDate']);
                } else {
                    $record['endDate'] = '';
                }

                $record['roles'] = json_decode($record['roles'], true);
                if (!$record['roles']) {
                    $record['roles'] = ['unknown'];
                }
            }
        }
        return $records;
    }

    public function getAuthorshipInfo($authorId, $type)
    {
        if ($records = $this->getAuthorshipRecords($authorId, $type)) {
            foreach ($records as $key => &$record) {
                if ($element = $this->structureManager->getElementById($record['elementId'])) {
                    $record[$type . 'Element'] = $element;
                } else {
                    unset($records[$key]);
                }
            }
        }
        return $records;
    }

    public function getAuthorshipRecords($authorId, $type = null)
    {
        $query = $this->db
            ->table('authorship')
            ->select('elementId', 'startDate', 'endDate', 'roles')
            ->where('authorId', '=', $authorId);
        if ($type) {
            $query->where('type', '=', $type);
        }
        if ($records = $query->get()) {
            foreach ($records as $key => &$record) {
                if ($record['startDate']) {
                    $record['startDate'] = date('d.m.Y', $record['startDate']);
                } else {
                    $record['startDate'] = '';
                }
                if ($record['endDate']) {
                    $record['endDate'] = date('d.m.Y', $record['endDate']);
                } else {
                    $record['endDate'] = '';
                }

                $record['roles'] = json_decode($record['roles'], true);
            }
        }
        return $records;
    }

    public function checkDuplicates($info)
    {
        if ($info) {
            if ($records = $this->db
                ->table('module_authoralias')
                ->whereIn('id', array_keys($info))
                ->get(['id', 'authorId'])
            ) {
                $foundAuthors = [];
                foreach ($records as $record) {
                    if (isset($foundAuthors[$record['authorId']])) {
                        //this is not the only alias of same author within list, let's delete it
                        unset($info[$record['id']]);
                    } else {
                        $foundAuthors[$record['authorId']] = true;
                        if (isset($info[$record['authorId']])) {
                            //main author should be removed if there is appropriate alias in list
                            if (!empty($info[$record['authorId']]['roles'])) {
                                $info[$record['id']]['roles'] = $info[$record['authorId']]['roles'];
                            }
                            unset($info[$record['authorId']]);
                        }
                    }
                }
            }
        }
        return $info;
    }

    public function checkAuthorship($elementId, $personId, $type, $roles = [], $startDate = 0, $endDate = 0)
    {
        if (is_array($roles)) {
            $roles = json_encode($roles);
        }
        if ($record = $this->db
            ->table('authorship')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $personId)
            ->where('type', '=', $type)
            ->first()
        ) {
            $data = [
                'roles' => $roles,
            ];
            if ($startDate) {
                $data['startDate'] = $startDate;
            }
            if ($endDate) {
                $data['endDate'] = $endDate;
            }
            $this->db
                ->table('authorship')
                ->where('elementId', '=', $elementId)
                ->where('authorId', '=', $personId)
                ->update($data);
        } else {
            $data = [
                'elementId' => $elementId,
                'type' => $type,
                'authorId' => $personId,
                'roles' => $roles,
            ];
            if ($startDate) {
                $data['startDate'] = $startDate;
            }
            if ($endDate) {
                $data['endDate'] = $endDate;
            }

            $this->db
                ->table('authorship')
                ->insert($data);
        }
    }

    public function deleteAuthorship($elementId, $authorId, $type)
    {
        if ($this->db
            ->table('authorship')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $authorId)
            ->where('type', '=', $type)
            ->delete()
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function moveAuthorship($newElementId, $recordIds)
    {
        $this->db
            ->table('authorship')
            ->whereIn('id', $recordIds)
            ->update(
                [
                    'elementId' => $newElementId,
                ]
            );
    }

    public function importAuthor($authorInfo, $origin, $createNew = true)
    {
        if (!isset($this->importedAuthors[$origin][$authorInfo['id']])) {
            /**
             * @var authorElement $element
             */
            if (!($element = $this->getElementByImportId($authorInfo['id'], $origin, 'author'))) {
                if ($element = $this->getAuthorByName($authorInfo['title'])) {
                    if ($origin) {
                        $this->saveImportId($element->id, $authorInfo['id'], $origin, 'author');
                    }
                    $this->updateAuthor($element, $authorInfo, $origin);
                } elseif ($createNew) {
                    $element = $this->createAuthor($authorInfo, $origin);
                }
            } else {
                $this->updateAuthor($element, $authorInfo, $origin);
            }
            $this->importedAuthors[$origin][$authorInfo['id']] = $element;
        }
        return $this->importedAuthors[$origin][$authorInfo['id']];
    }

    /**
     * @param array $authorInfo
     * @param $origin
     * @return bool|authorElement
     */
    protected function createAuthor($authorInfo, $origin)
    {
        $element = false;
        $firstLetter = mb_strtolower(mb_substr($authorInfo['title'], 0, 1));
        $translitHelper = new TranslitHelper();
        $firstLetter = mb_substr($translitHelper->convert($firstLetter), 0, 1);
        if (!preg_match('/[a-zA-Z]/', $firstLetter)) {
            $firstLetter = '#';
        }
        if ($authorsElement = $this->structureManager->getElementByMarker('authors')) {
            $authorLetterElement = null;
            /**
             * @var letterElement[] $letters
             */
            $letters = $this->structureManager->getElementsChildren($authorsElement->id);
            foreach ($letters as $letterElement) {
                if (mb_strtolower($letterElement->title) == $firstLetter) {
                    $authorLetterElement = $letterElement;
                    break;
                }
            }
            if ($authorLetterElement) {
                /**
                 * @var authorElement $authorElement
                 */
                if ($element = $this->structureManager->createElement('author', 'show', $authorLetterElement->id)) {
                    /**
                     * @var authorElement $element
                     */
                    $this->updateAuthor($element, $authorInfo, $origin);
                    if ($origin) {
                        $this->saveImportId($element->id, $authorInfo['id'], $origin, 'author');
                    }
                }
            }
        }
        return $element;
    }

    /**
     * @param authorElement $element
     * @param $authorInfo
     * @param $origin
     */
    protected function updateAuthor($element, $authorInfo, $origin)
    {
        $changed = false;
        if (!empty($authorInfo['title']) && $element->title != $authorInfo['title']) {
            if (!$element->title) {
                $changed = true;
                $element->title = $authorInfo['title'];
                $element->structureName = $authorInfo['title'];
            }
        }
        if (!empty($authorInfo['locationLabel'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($authorInfo['locationLabel'])) {
                if ($locationElement->structureType == 'country') {
                    if (!$element->country || $this->forceUpdateCountry) {
                        if ($element->country != $locationElement->id) {
                            $changed = true;
                            $element->country = $locationElement->id;
                        }
                    }
                } elseif ($locationElement->structureType == 'city') {
                    if (!$element->city || $this->forceUpdateCity) {
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
        }
        if ($this->forceUpdateCountry && !empty($authorInfo['countryId'])) {
            $countryElement = $this->getElementByImportId($authorInfo['countryId'], $origin, 'country');
            if ($countryElement && $element->country != $countryElement->id) {
                $changed = true;
                $element->country = $countryElement->id;
            }
        }

        if ($changed) {
            $element->checkCountry();
            $element->persistElementData();
        }

        if ($this->forceUpdateGroups && isset($authorInfo['groups'])) {
            foreach ($authorInfo['groups'] as $groupId) {
                if ($groupElement = $this->getElementByImportId($groupId, $origin, 'group')) {
                    $this->checkAuthorship($groupElement->id, $element->getId(), 'group', []);
                }
            }
        }
    }

    public function importAuthorAlias($authorAliasInfo, $origin, $createNew = true)
    {
        if (!isset($this->importedAuthorAliases[$origin][$authorAliasInfo['id']])) {
            /**
             * @var authorAliasElement $element
             */
            if (!($element = $this->getElementByImportId($authorAliasInfo['id'], $origin, 'author'))) {
                if ($element = $this->getAuthorAliasByName($authorAliasInfo['title'])) {
                    $this->saveImportId($element->id, $authorAliasInfo['id'], $origin, 'author');
                    $this->updateAuthorAlias($element, $authorAliasInfo, $origin);
                } elseif ($createNew) {
                    $element = $this->createAuthorAlias($authorAliasInfo, $origin);
                }
            } else {
                $this->updateAuthorAlias($element, $authorAliasInfo, $origin);
            }
            $this->importedAuthorAliases[$origin][$authorAliasInfo['id']] = $element;
        }
        return $this->importedAuthorAliases[$origin][$authorAliasInfo['id']];
    }

    /**
     * @param array $authorAliasInfo
     * @param $origin
     * @return bool|authorAliasElement
     */
    protected function createAuthorAlias($authorAliasInfo, $origin)
    {
        if ($element = $this->manufactureAliasElement($authorAliasInfo['title'])) {
            $this->updateAuthorAlias($element, $authorAliasInfo, $origin);
            $this->saveImportId($element->id, $authorAliasInfo['id'], $origin, 'author');
        }
        return $element;
    }

    /**
     * @param string $title
     * @return authorAliasElement|bool
     */
    protected function manufactureAliasElement($title = '')
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                if ($element = $this->structureManager->createElement('authorAlias', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return false;
    }

    public function manufactureAuthorElement($title)
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                if ($element = $this->structureManager->createElement('author', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return false;
    }

    /**
     * @param authorAliasElement $element
     * @param $authorAliasInfo
     * @param $origin
     */
    protected function updateAuthorAlias($element, $authorAliasInfo, $origin)
    {
        $changed = false;

        if (!empty($authorAliasInfo['title']) && !$element->title) {
            if (!$element->title) {
                $changed = true;

                $element->title = $authorAliasInfo['title'];
                $element->structureName = $authorAliasInfo['title'];
            }
        }
        if (!empty($authorAliasInfo['authorId'])) {
            if (isset($authorAliasInfo['authorId']) && !$element->authorId) {
                $authorElement = $this->getElementByImportId($authorAliasInfo['authorId'], $origin, 'author');
                if (!empty($authorElement) && $authorElement->id != $element->authorId) {
                    $changed = true;
                    $element->authorId = $authorElement->id;
                }
            }
        }
        if ($changed) {
            $element->persistElementData();
        }
    }

    public function joinDeleteAuthor($mainAuthorId, $joinedAuthorId)
    {
        $this->joinAuthor($mainAuthorId, $joinedAuthorId, false);
    }

    public function joinAuthorAsAlias($mainAuthorId, $joinedAuthorId)
    {
        $this->joinAuthor($mainAuthorId, $joinedAuthorId, true);
    }

    protected function joinAuthor($mainAuthorId, $joinedAuthorId, $makeAlias = true)
    {
        if ($joinedAuthorId == $mainAuthorId) {
            return false;
        }
        /**
         * @var authorAliasElement|authorElement $mainAuthor
         */
        if ($mainAuthor = $this->structureManager->getElementById($mainAuthorId)) {
            /**
             * @var authorAliasElement|authorElement $joinedAuthor
             */
            if ($joinedAuthor = $this->structureManager->getElementById($joinedAuthorId)) {
                /**
                 * @var authorAliasElement|authorElement $targetAuthorElement
                 */
                if ($makeAlias) {
                    $targetAuthorElement = $this->manufactureAliasElement($joinedAuthor->title);
                    $targetAuthorElement->title = $joinedAuthor->title;
                    $targetAuthorElement->structureName = $joinedAuthor->title;
                    $targetAuthorElement->authorId = $mainAuthorId;
                } else {
                    $targetAuthorElement = $mainAuthor;
                }

                if ($targetAuthorElement) {
                    if ($aliasElements = $joinedAuthor->getAliasElements()) {
                        /**
                         * @var authorAliasElement $aliasElement
                         */
                        foreach ($aliasElements as $aliasElement) {
                            $aliasElement->authorId = $mainAuthorId;
                            $aliasElement->persistElementData();
                        }
                    }

                    $this->privilegesManager->copyPrivileges($joinedAuthor->id, $mainAuthorId);

                    if ($links = $this->linksManager->getElementsLinks($joinedAuthorId, null, 'parent')) {
                        foreach ($links as $link) {
                            $this->linksManager->unLinkElements($joinedAuthorId, $link->childStructureId, $link->type);
                            $this->linksManager->linkElements(
                                $targetAuthorElement->getId(),
                                $link->childStructureId,
                                $link->type
                            );
                        }
                    }
                    foreach ($this->languagesManager->getLanguagesIdList() as $languageId) {
                        if (!$mainAuthor->getValue('realName', $languageId)) {
                            if ($joinedValue = $joinedAuthor->getValue('realName', $languageId)) {
                                $mainAuthor->setValue('realName', $joinedValue, $languageId);
                            }
                        }
                    }
                    if ($joinedAuthor->structureType == 'author') {
                        if (!$mainAuthor->country) {
                            $mainAuthor->country = $joinedAuthor->country;
                        }
                        if (!$mainAuthor->city) {
                            $mainAuthor->city = $joinedAuthor->city;
                        }
                        if (!$mainAuthor->artCityId) {
                            $mainAuthor->artCityId = $joinedAuthor->artCityId;
                        }
                        if (empty($mainAuthor->zxTunesId) && !empty($joinedAuthor->zxTunesId)) {
                            $mainAuthor->zxTunesId = $joinedAuthor->zxTunesId;
                        }
                        if (!$mainAuthor->wikiLink) {
                            $mainAuthor->wikiLink = $joinedAuthor->wikiLink;
                        }
                        if (!$mainAuthor->email) {
                            $mainAuthor->email = $joinedAuthor->email;
                        }
                        if (!$mainAuthor->site) {
                            $mainAuthor->site = $joinedAuthor->site;
                        }
                        $mainAuthor->persistElementData();
                    } else {
                        $mainAuthor->persistElementData();
                    }

                    if ($targetAuthorElement !== $mainAuthor) {
                        $targetAuthorElement->persistElementData();
                    }

                    //disabled authorship moving to new author
                    //check if author already has authorship in same elements. we dont need duplicates
                    $existingElements = [];
                    if ($existingAuthorShipRecords = $this->getAuthorshipRecords($mainAuthorId)) {
                        foreach ($existingAuthorShipRecords as $record) {
                            $existingElements[] = $record['elementId'];
                        }
                    }

                    //delete duplicates from joined author
                    if ($existingElements) {
                        $this->db->table('authorship')
                            ->where('authorId', '=', $joinedAuthorId)
                            ->whereIn('elementId', $existingElements)
                            ->delete();
                    }

                    //now move all remaining records to main author
                    $this->db->table('authorship')
                        ->where('authorId', '=', $joinedAuthorId)
                        ->update(['authorId' => $targetAuthorElement->id]);

                    $this->db->table('import_origin')
                        ->where('elementId', '=', $joinedAuthorId)
                        ->update(['elementId' => $targetAuthorElement->id]);

                    $joinedAuthor->deleteElementData();
                }
            }
        }
        return true;
    }

    public function convertAliasToAuthor($aliasId)
    {
        $newAuthorElement = false;
        /**
         * @var authorAliasElement $aliasElement
         */
        if ($aliasElement = $this->structureManager->getElementById($aliasId)) {
            /**
             * @var authorElement $newAuthorElement
             */
            if ($newAuthorElement = $this->manufactureAuthorElement($aliasElement->title)) {
                $this->privilegesManager->copyPrivileges($aliasId, $aliasId);

                if ($links = $this->linksManager->getElementsLinks($aliasId, null, 'parent')) {
                    foreach ($links as $link) {
                        $this->linksManager->unLinkElements($aliasId, $link->childStructureId, $link->type);
                        $this->linksManager->linkElements(
                            $newAuthorElement->getId(),
                            $link->childStructureId,
                            $link->type
                        );
                    }
                }

                $newAuthorElement->title = $aliasElement->title;
                $newAuthorElement->structureName = $aliasElement->title;
                $newAuthorElement->persistElementData();

                $this->db->table('authorship')
                    ->where('authorId', '=', $aliasId)
                    ->update(['authorId' => $newAuthorElement->id]);

                $this->db->table('import_origin')
                    ->where('elementId', '=', $aliasId)
                    ->update(['elementId' => $newAuthorElement->id]);

                $aliasElement->deleteElementData();
            }
        }

        return $newAuthorElement;
    }

    protected function getLettersListMarker($type)
    {
        if ($type == 'admin') {
            return 'authors';
        } else {
            return 'authorsmenu';
        }
    }


    /**
     * @param groupElement $groupElement
     * @return bool|authorElement
     */
    public function convertGroupToAuthor($groupElement)
    {
        $authorElement = false;
        if ($groupElement->structureType == 'group') {
            if ($authorElement = $this->manufactureAuthorElement($groupElement->title)) {
                $authorElement->title = $groupElement->title;
                $authorElement->structureName = $groupElement->title;
                $authorElement->country = $groupElement->country;
                $authorElement->city = $groupElement->city;
                $authorElement->persistElementData();

                foreach ($groupElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($authorElement->id, $zxProd->id, 'zxProdPublishers');
                }
                foreach ($groupElement->getGroupProds() as $zxProd) {
                    $this->checkAuthorship($zxProd->id, $authorElement->id, 'prod', []);
                }
                foreach ($groupElement->publishedReleases as $zxRelease) {
                    $this->checkAuthorship($zxRelease->id, $authorElement->id, 'release', ['release']);
                }

                if ($records = $this->db->table('import_origin')
                    ->where('elementId', '=', $groupElement->id)
                    ->get()) {
                    foreach ($records as $record) {
                        if (!$this->db->table('import_origin')
                            ->where('type', '=', 'author')
                            ->where('importOrigin', '=', $record['importOrigin'])
                            ->where('importId', '=', $record['importId'])
                            ->limit(1)
                            ->get()) {
                            $this->db->table('import_origin')
                                ->where('elementId', '=', $groupElement->id)
                                ->update(
                                    [
                                        'elementId' => $authorElement->id,
                                        'type' => 'author',
                                    ]
                                );
                        } else {
                            $this->db->table('import_origin')
                                ->where('id', '=', $record['id'])->delete();
                        }
                    }
                }


                $groupElement->deleteElementData();
            }
        }
        return $authorElement;
    }
}