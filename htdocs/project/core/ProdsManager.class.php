<?php

class ProdsManager extends ElementsManager
{
    const TABLE = 'module_zxprod';
    use ImportIdOperatorTrait;
    use ReleaseFormatsProvider;
    use ReleaseFileTypesGatherer;

    protected $forceUpdateYear = false;
    protected $forceUpdateYoutube = false;
    protected $forceUpdateExternalLink = false;
    protected $forceUpdateCategories = false;
    protected $forceUpdateImages = false;
    protected $forceUpdateTitles = false;

    /**
     * @param bool $forceUpdateExternalLink
     */
    public function setForceUpdateExternalLink(bool $forceUpdateExternalLink): void
    {
        $this->forceUpdateExternalLink = $forceUpdateExternalLink;
    }

    protected $forceUpdateAuthors = false;
    protected $forceUpdateGroups = false;
    protected $forceUpdatePublishers = false;
    protected $updateExistingProds = false;
    protected $addImages = false;
    protected $resizeImages = false;

    /**
     * @param bool $resizeImages
     */
    public function setResizeImages(bool $resizeImages): void
    {
        $this->resizeImages = $resizeImages;
    }

    protected $columnRelations = [];
    protected $releaseColumnRelations = [];
    /**
     * @var PartiesManager
     */
    protected $partiesManager;
    /**
     * @var GroupsManager
     */
    protected $groupsManager;
    /**
     * @var ZxParsingManager
     */
    protected $zxParsingManager;
    /**
     * @var AuthorsManager
     */
    protected $authorsManager;
    /**
     * @var linksManager
     */
    protected $linksManager;
    /**
     * @var ProdsDownloader
     */
    protected $prodsDownloader;
    /**
     * @var privilegesManager
     */
    protected $privilegesManager;
    /**
     * @var PathsManager
     */
    protected $pathsManager;

    public function __construct()
    {
        $this->columnRelations = [
            'title' => ['LOWER(title)' => true],
            'place' => ['if(partyplace,0,1), partyplace' => true],
            'date' => ['dateCreated' => true, 'id' => true],
            'year' => ['year' => true, 'dateAdded' => true, 'id' => true],
            'votes' => ['votes' => true, 'if(partyplace,0,1), partyplace' => false, 'title' => true],
        ];
        $this->releaseColumnRelations = [
            'title' => ['LOWER(title)' => true],
            'date' => ['dateCreated' => true, 'id' => true],
        ];
    }

    /**
     * @param bool $forceUpdateImages
     */
    public function setForceUpdateImages(bool $forceUpdateImages): void
    {
        $this->forceUpdateImages = $forceUpdateImages;
    }

    /**
     * @param bool $updateExistingProds
     */
    public function setUpdateExistingProds($updateExistingProds)
    {
        $this->updateExistingProds = $updateExistingProds;
    }

    /**
     * @param bool $forceUpdateYear
     */
    public function setForceUpdateYear($forceUpdateYear)
    {
        $this->forceUpdateYear = $forceUpdateYear;
    }

    /**
     * @param bool $forceUpdateYoutube
     */
    public function setForceUpdateYoutube($forceUpdateYoutube)
    {
        $this->forceUpdateYoutube = $forceUpdateYoutube;
    }

    /**
     * @param bool $forceUpdateGroups
     */
    public function setForceUpdateGroups($forceUpdateGroups)
    {
        $this->forceUpdateGroups = $forceUpdateGroups;
    }

    /**
     * @param bool $forceUpdatePublishers
     */
    public function setForceUpdatePublishers($forceUpdatePublishers)
    {
        $this->forceUpdatePublishers = $forceUpdatePublishers;
    }

    /**
     * @param privilegesManager $privilegesManager
     */
    public function setPrivilegesManager($privilegesManager)
    {
        $this->privilegesManager = $privilegesManager;
    }

    /**
     * @param PartiesManager $partiesManager
     */
    public function setPartiesManager($partiesManager)
    {
        $this->partiesManager = $partiesManager;
    }

    /**
     * @param bool $forceUpdateAuthors
     */
    public function setForceUpdateAuthors($forceUpdateAuthors)
    {
        $this->forceUpdateAuthors = $forceUpdateAuthors;
    }

    /**
     * @param PathsManager $pathsManager
     */
    public function setPathsManager(PathsManager $pathsManager): void
    {
        $this->pathsManager = $pathsManager;
    }

    /**
     * @param bool $forceUpdateCategories
     */
    public function setForceUpdateCategories($forceUpdateCategories)
    {
        $this->forceUpdateCategories = $forceUpdateCategories;
    }

    /**
     * @param bool $addImages
     */
    public function setAddImages($addImages)
    {
        $this->addImages = $addImages;
    }

    /**
     * @param bool $forceUpdateTitles
     */
    public function setForceUpdateTitles($forceUpdateTitles)
    {
        $this->forceUpdateTitles = $forceUpdateTitles;
    }

    /**
     * @param ZxParsingManager $zxParsingManager
     */
    public function setZxParsingManager($zxParsingManager)
    {
        $this->zxParsingManager = $zxParsingManager;
    }

    /**
     * @param ProdsDownloader $prodsDownloader
     */
    public function setProdsDownloader($prodsDownloader)
    {
        $this->prodsDownloader = $prodsDownloader;
    }

    /**
     * @param GroupsManager $groupsManager
     */
    public function setGroupsManager($groupsManager)
    {
        $this->groupsManager = $groupsManager;
    }

    /**
     * @param AuthorsManager $authorsManager
     */
    public function setAuthorsManager($authorsManager)
    {
        $this->authorsManager = $authorsManager;
    }

    /**
     * @param linksManager $linksManager
     */
    public function setLinksManager($linksManager)
    {
        $this->linksManager = $linksManager;
    }


    /**
     * @param $prodInfo
     * @param $origin
     * @return bool|zxProdElement
     */
    public function importProd($prodInfo, $origin)
    {
        /**
         * @var zxProdElement $element
         */
        $prodId = $prodInfo['id'];
        $prodInfo['title'] = $this->sanitizeTitle($prodInfo['title']);
        $element = null;
        if (!$element) {
            $element = $this->getElementByImportId($prodId, $origin, 'prod');
        }
        if (!$element) {
            if (isset($prodInfo['ids'])) {
                foreach ($prodInfo['ids'] as $idOrigin => $id) {
                    if ($element = $this->getElementByImportId($id, $idOrigin, 'prod')) {
                        $this->saveImportId($element->id, $prodId, $origin, 'prod');
                        break;
                    }
                }
            }
        }
        if (!$element) {
            if ($element = $this->getProdByReleaseMd5($prodInfo)) {
                $this->saveImportId($element->id, $prodId, $origin, 'prod');
            }
        }
        if (!$element) {
            if ($element = $this->findProdBestMatch($prodInfo)) {
                $this->saveImportId($element->id, $prodId, $origin, 'prod');
            }
        }

        if (!$element) {
            return $this->createProd($prodInfo, $origin);
        }

        if ($this->updateExistingProds) {
            return $this->updateProd($element, $prodInfo, $origin);
        } else {
            return $element;
        }
    }

    /**
     * @param array $prodInfo
     * @param $origin
     * @return bool|zxProdElement
     */
    protected function createProd($prodInfo, $origin)
    {
        $category = null;
        if ($prodInfo['directCategories']) {
            $category = reset($prodInfo['directCategories']);
        }
        /**
         * @var zxProdElement $element
         */
        if ($element = $this->structureManager->createElement('zxProd', 'show', $category)) {
            $element->dateAdded = time();
            $this->saveImportId($element->getId(), $prodInfo['id'], $origin, 'prod');
            $this->updateProd($element, $prodInfo, $origin, true);
        }

        return $element;
    }

    protected function importLabelsInfo($infoIndex, $origin)
    {
        $infoIndex = array_reverse($infoIndex);
        foreach ($infoIndex as $gatheredInfo) {
            if ($gatheredInfo['isAlias'] && $gatheredInfo['isGroup']) {
                $this->groupsManager->importGroupAlias($gatheredInfo, $origin);
            } elseif ($gatheredInfo['isAlias'] && $gatheredInfo['isPerson']) {
                $this->authorsManager->importAuthorAlias($gatheredInfo, $origin);
            } elseif ($gatheredInfo['isGroup']) {
                $this->groupsManager->importGroup($gatheredInfo, $origin);
            } elseif ($gatheredInfo['isPerson']) {
                $this->authorsManager->importAuthor($gatheredInfo, $origin);
            } else {
                //we don't know anything about this label. lets search for any group with that name
                $result = $this->groupsManager->importGroup($gatheredInfo, $origin, false);
                if (!$result) {
                    //search for author alias with that name
                    $result = $this->authorsManager->importAuthorAlias($gatheredInfo, $origin, false);
                }
                if (!$result) {
                    //search for group alias with that name
                    $result = $this->groupsManager->importGroupAlias($gatheredInfo, $origin, false);
                }
                if (!$result) {
                    //just create author by default.
                    $this->authorsManager->importAuthor($gatheredInfo, $origin);
                }
            }
        }
    }

    /**
     * @param zxProdElement $element
     * @param $prodInfo
     * @param $origin
     * @return zxProdElement
     */
    protected function updateProd($element, $prodInfo, $origin, $justCreated = false)
    {
        $changed = false;
        if (!empty($prodInfo['title']) && ($element->title != $prodInfo['title'])) {
            if (!$element->title || $this->forceUpdateTitles) {
                $changed = true;
                $element->title = $prodInfo['title'];
                $element->structureName = $prodInfo['title'];
            }
        }
        if (!empty($prodInfo['legalStatus']) && $element->legalStatus != $prodInfo['legalStatus']) {
            $changed = true;
            $element->legalStatus = $prodInfo['legalStatus'];
        }
        if (!empty($prodInfo['year']) && (($element->year != $prodInfo['year']) && ($this->forceUpdateYear || $justCreated))) {
            $changed = true;
            $element->year = $prodInfo['year'];
        }
        if (!empty($prodInfo['compo']) && ($element->compo != $prodInfo['compo'])) {
            $changed = true;
            $element->compo = $prodInfo['compo'];
        }
        if (!empty($prodInfo['party']) && (!$element->party || (!empty($prodInfo['party']['place']) && !$element->partyplace))) {
            if (($partyTitle = $prodInfo['party']['title']) && ($partyYear = $prodInfo['party']['year'])) {
                if ($partyElement = $this->partiesManager->getPartyByTitle($partyTitle, $partyYear)) {
                    if ($element->party != $partyElement->id) {
                        $changed = true;
                        $element->party = $partyElement->id;
                        $element->renewPartyLink();
                    }
                    if ($partyPlace = $prodInfo['party']['place']) {
                        if ($element->partyplace != $partyPlace) {
                            $element->partyplace = $partyPlace;
                            $changed = true;
                        }
                    }
                }
            }
        }
        if (!empty($prodInfo['youtubeId']) && (($element->youtubeId != $prodInfo['youtubeId']) && ($this->forceUpdateYoutube || $justCreated))) {
            $changed = true;
            $element->youtubeId = $prodInfo['youtubeId'];
        }
        if (!empty($prodInfo['externalLink']) && (($element->externalLink != $prodInfo['externalLink']) && ($this->forceUpdateExternalLink || $justCreated))) {
            $changed = true;
            $element->externalLink = $prodInfo['externalLink'];
        }
        if (!empty($prodInfo['language']) && $element->language != $prodInfo['language']) {
            $changed = true;
            $element->language = $prodInfo['language'];
        }
        if ($changed) {
            $element->persistElementData();
        }
        if (!empty($prodInfo['labels'])) {
            $this->importLabelsInfo($prodInfo['labels'], $origin);
        }
        if (!empty($prodInfo['directCategories'])) {
            if ($this->forceUpdateCategories || $justCreated || !$element->getConnectedCategoriesIds()) {
                foreach ($prodInfo['directCategories'] as $categoryId) {
                    $this->linksManager->linkElements($categoryId, $element->id, 'zxProdCategory');
                }
            }
        }

        foreach ($prodInfo['releases'] as $releaseInfo) {
            $this->importRelease($releaseInfo, $prodInfo['id'], $origin);
        }

        if (!empty($prodInfo['undetermined'])) {
            foreach ($prodInfo['undetermined'] as $undeterminedId => $roles) {
                if ($elementId = $this->getElementIdByImportId($undeterminedId, $origin, 'group')) {
                    $prodInfo['groups'][] = $undeterminedId;
                } elseif ($elementId = $this->getElementIdByImportId($undeterminedId, $origin, 'author')) {
                    $prodInfo['authors'][$undeterminedId] = $roles;
                }
            }
        }

        if (($this->forceUpdateAuthors || $justCreated) && !empty($prodInfo['authors'])) {
            if (!$element->getAuthorsInfo('prod')) {
                foreach ($prodInfo['authors'] as $importAuthorId => $roles) {
                    if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                        $this->authorsManager->checkAuthorship($element->id, $authorId, 'prod', $roles);
                    }
                }
            }
        }
        if (($this->forceUpdateGroups || $justCreated) && !empty($prodInfo['groups'])) {
            if (!$element->groups) {
                $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdGroups', 'child');
                foreach ($prodInfo['groups'] as $importGroupId) {
                    if ($groupId = $this->getElementIdByImportId($importGroupId, $origin, 'group')) {
                        if (!isset($linksIndex[$groupId])) {
                            $this->linksManager->linkElements($groupId, $element->id, 'zxProdGroups');
                        }
                        unset($linksIndex[$groupId]);
                    }
                }
                foreach ($linksIndex as $key => &$link) {
                    $link->delete();
                }
            }
        }
        if (($this->forceUpdatePublishers || $justCreated) && !empty($prodInfo['publishers'])) {
            if (!$element->publishers) {
                $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdPublishers', 'child');
                foreach ($prodInfo['publishers'] as $importPublisherId) {
                    if (($publisherId = $this->getElementIdByImportId($importPublisherId, $origin, 'group'))
                        || ($publisherId = $this->getElementIdByImportId($importPublisherId, $origin, 'author'))) {
                        if (!isset($linksIndex[$publisherId])) {
                            $this->linksManager->linkElements($publisherId, $element->id, 'zxProdPublishers');
                        }
                        unset($linksIndex[$publisherId]);
                    }
                }
                foreach ($linksIndex as $key => &$link) {
                    $link->delete();
                }
            }
        }
        if (!empty($prodInfo['compilations'])) {
            if (!$element->compilationProds) {
                foreach ($prodInfo['compilations'] as $importProdId) {
                    if ($prodId = $this->getElementIdByImportId($importProdId, $origin, 'prod')) {
                        $this->linksManager->linkElements($element->id, $prodId, 'compilation');
                    }
                }
            }
        }
        if (!empty($prodInfo['categories']) && (!$element->getConnectedCategoriesIds() || $this->forceUpdateCategories || $justCreated)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdCategory', 'child');
            foreach ($prodInfo['categories'] as $importCategoryId) {
                if ($categoryId = $this->getElementIdByImportId($importCategoryId, $origin, 'category')) {
                    if (!isset($linksIndex[$categoryId])) {
                        $this->linksManager->linkElements($categoryId, $element->id, 'zxProdCategory');
                    }
                    unset($linksIndex[$categoryId]);
                }
            }
            foreach ($linksIndex as $link) {
                $link->delete();
            }

        }
        if (!empty($prodInfo['images']) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('connectedFile'))) {
            $this->importElementFiles($element, $prodInfo['images']);
            if ($this->resizeImages) {
                $element->resizeImages();
            }
        }

        if (!empty($prodInfo['maps'])) {
            foreach ($prodInfo['maps'] as $map) {
                $this->importElementFile($element, $map['url'], $map['author'], 'mapFilesSelector');
            }
        }

        if (!empty($prodInfo['rzx'])) {
            foreach ($prodInfo['rzx'] as $rzx) {
                $this->importElementFile($element, $rzx['url'], $rzx['author'], 'rzx');
            }
        }

        if (!empty($prodInfo['importIds'])) {
            foreach ($prodInfo['importIds'] as $origin => $id) {
                if (!$this->getElementIdByImportId($id, $origin, 'prod')) {
                    $this->saveImportId($element->getId(), $id, $origin, 'prod');
                }
            }
        }

        return $element;
    }

    private function importElementFile($element, $fileUrl, $fileAuthor = '', $propertyName = 'connectedFile')
    {
        $this->structureManager->setNewElementLinkType($element->getConnectedFileType($propertyName));
        $existingFiles = $element->getFilesList($propertyName);
        if (!$existingFiles || $this->addImages) {
            $uploadsPath = $this->pathsManager->getPath('uploads');

            $originalFileName = basename($fileUrl);
            $fileExists = false;
            foreach ($existingFiles as $existingFile) {
                if ($originalFileName == urldecode($existingFile->fileName)) {
                    $fileExists = true;
                    break;
                }
            }

            /**
             * @var fileElement $fileElement
             */
            if (!$fileExists) {
                $filePath = $uploadsPath . $originalFileName;
                $this->prodsDownloader->downloadUrl($fileUrl, $filePath);
                if ($filePath && $fileElement = $this->structureManager->createElement(
                        'file',
                        'showForm',
                        $element->getId()
                    )) {
                    $destinationFolder = $element->getUploadedFilesPath($propertyName);

                    $info = pathinfo($fileUrl);
                    $fileElement->title = str_replace('_', ' ', ucfirst($info['filename']));
                    $fileElement->structureName = $fileElement->title;
                    $fileElement->file = $fileElement->getId();
                    $fileElement->fileName = $originalFileName;
                    $fileElement->author = $fileAuthor;
                    rename($filePath, $destinationFolder . $fileElement->file);
                    $fileElement->persistElementData();

                    $element->appendFileToList($fileElement, $propertyName);
                }
            }
        }
        $this->structureManager->setNewElementLinkType();

    }

    /**
     * @param FilesElementTrait $element
     * @param $images
     * @param string $propertyName
     */
    protected function importElementFiles($element, $images, $propertyName = 'connectedFile')
    {
        foreach ($images as $imageUrl) {
            $this->importElementFile($element, $imageUrl, '', $propertyName);
        }
    }

    protected function linkReleaseWithAuthor($authorId, $prodId, $roles = [])
    {
        $this->authorsManager->checkAuthorship($prodId, $authorId, 'release', $roles);
    }

    protected function linkReleaseWithPublisher($publisherId, $prodId)
    {
        $this->linksManager->linkElements($publisherId, $prodId, 'zxReleasePublishers');
    }

    /**
     * @param $prodInfo
     * @return bool|zxProdElement
     */
    protected function getProdByReleaseMd5($prodInfo)
    {
        foreach ($prodInfo['releases'] as $releaseInfo) {
            if ($releaseElement = $this->getReleaseByMd5($releaseInfo)) {
                return $releaseElement->getProd();
            }
        }
        return false;
    }

    protected function findProdBestMatch($prodInfo)
    {
        if (!empty($prodInfo['year'])) {
            $query = $this->db->table('module_zxprod')
                ->where(
                    function ($query) use ($prodInfo) {
                        $query->orWhere('title', '=', htmlentities($prodInfo['title'], ENT_QUOTES));
                        $query->orWhere('title', '=', $prodInfo['title']);
                    }
                );
            $query->where('year', '=', $prodInfo['year']);
            if ($id = $query->value('id')) {
                return $this->structureManager->getElementById($id);
            }
        }
        return false;
    }

    /**
     * @param $releaseInfo
     * @return bool|zxReleaseElement
     */
    protected function getReleaseByMd5($releaseInfo)
    {
        if (empty($releaseInfo['md5'])) {
            if ($path = $this->prodsDownloader->getDownloadedPath($releaseInfo['fileUrl'])) {
                if ($structure = $this->zxParsingManager->getFileStructure($path)) {
                    $releaseFiles = $this->gatherReleaseFiles($structure);
                    $index = [];
                    if ($records = $this->db->table('files_registry')
                        ->whereIn('md5', array_keys($releaseFiles))
                        ->get()) {
                        $foundReleaseId = false;
                        foreach ($records as $record) {
                            $index[$record['elementId']][$record['md5']] = true;
                        }
                        foreach ($index as $elementId => $md5Index) {
                            if (count($index[$elementId]) == count($releaseFiles)) {
                                $foundReleaseId = $elementId;
                                break;
                            }
                        }
                        return $this->structureManager->getElementById($foundReleaseId);
                    }
                }
            }
        }
        return false;
    }


    public function importRelease($releaseInfo, $prodId, $origin)
    {
        $releaseId = $releaseInfo['id'];
        $releaseInfo['title'] = $this->sanitizeTitle($releaseInfo['title']);
        /**
         * @var zxReleaseElement $element
         */
        $element = $this->getElementByImportId($releaseId, $origin, 'release');
        if (!$element) {
            if ($element = $this->getReleaseByMd5($releaseInfo)) {
                $this->saveImportId($element->id, $releaseId, $origin, 'release');
            }
        }
        if (!$element) {
            return $this->createRelease($releaseInfo, $prodId, $origin);
        }
        if ($element) {
            $this->updateRelease($element, $releaseInfo, $origin);
        }
        return $element;
    }

    /**
     * @param array $releaseInfo
     * @param $prodId
     * @param $origin
     * @return bool|zxReleaseElement
     */
    protected function createRelease($releaseInfo, $prodId, $origin)
    {
        $element = false;
        if ($prodElement = $this->getElementByImportId($prodId, $origin, 'prod')) {
            if ($element = $this->structureManager->createElement('zxRelease', 'show', $prodElement->id)) {
                $element->persistStructureLinks();
                /**
                 * @var zxReleaseElement $element
                 */
                $this->saveImportId($element->getId(), $releaseInfo['id'], $origin, 'release');
                $this->updateRelease($element, $releaseInfo, $origin);
            }
        }

        return $element;
    }

    /**
     * @param zxReleaseElement $element
     * @param $releaseInfo
     * @param $origin
     */
    protected function updateRelease($element, $releaseInfo, $origin)
    {
        $changed = false;
        if (($this->forceUpdateTitles || !$element->title) && !empty($releaseInfo['title'])) {
            if (!$element->title || $this->forceUpdateTitles) {
                $element->title = $releaseInfo['title'];
                $element->structureName = $releaseInfo['title'];
                $changed = true;
            }
        }
        if ((!$element->year || $this->forceUpdateYear) && !empty($releaseInfo['year'])) {
            $element->year = $releaseInfo['year'];
            $changed = true;
        }
        if (!$element->hardwareRequired && !empty($releaseInfo['hardwareRequired'])) {
            $element->hardwareRequired = $releaseInfo['hardwareRequired'];
            $changed = true;
        }
        if ((!$element->releaseType || $element->releaseType === 'unknown') && !empty($releaseInfo['releaseType'])) {
            $element->releaseType = $releaseInfo['releaseType'];
            $changed = true;
        }
        if (!$element->language && !empty($releaseInfo['language'])) {
            $element->language = $releaseInfo['language'];
            $changed = true;
        }
        if (!$element->version && !empty($releaseInfo['version'])) {
            $element->version = $releaseInfo['version'];
            $changed = true;
        }
        if (!empty($releaseInfo['filePath'])) {
            $destinationFolder = $element->getUploadedFilesPath();

            $info = pathinfo($releaseInfo['filePath']);
            $element->file = $element->getId();
            $element->fileName = $info['filename'] . '.' . $info['extension'];
            $element->parsed = 0;

            $changed = true;

            copy($releaseInfo['filePath'], $destinationFolder . $element->file);
        } elseif (!empty($releaseInfo['fileUrl'])) {
            $info = pathinfo($releaseInfo['fileUrl']);
            $fileName = $info['filename'] . '.' . $info['extension'];

            if ($element->fileName != $fileName || !is_file($element->getFilePath())) {
                $changed = true;

                $element->file = $element->getId();
                $element->fileName = $fileName;
                $element->parsed = 0;

                $this->prodsDownloader->moveFileContents($element->getFilePath(), $releaseInfo['fileUrl']);
            }
        }
        if (!empty($releaseInfo['labels'])) {
            $this->importLabelsInfo($releaseInfo['labels'], $origin);
        }

        if (!empty($releaseInfo['undetermined'])) {
            foreach ($releaseInfo['undetermined'] as $undeterminedId => $roles) {
                if ($this->getElementIdByImportId($undeterminedId, $origin, 'group')) {
                    $releaseInfo['publishers'][] = $undeterminedId;
                } elseif ($this->getElementIdByImportId($undeterminedId, $origin, 'author')) {
                    $releaseInfo['authors'][$undeterminedId] = $roles;
                }
            }
        }

        if (!empty($releaseInfo['authors'])) {
            foreach ($releaseInfo['authors'] as $importAuthorId => $roles) {
                if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                    $this->linkReleaseWithAuthor($authorId, $element->id, $roles);
                }
            }
        }
        if (!empty($releaseInfo['publishers'])) {
            if (!$element->getPublishersIds()) {
                foreach ($releaseInfo['publishers'] as $importPublisherId) {
                    if ($publisherId = $this->getElementIdByImportId($importPublisherId, $origin, 'group')) {
                        $this->linkReleaseWithPublisher($publisherId, $element->id);
                    } elseif ($publisherId = $this->getElementIdByImportId($importPublisherId, $origin, 'author')) {
                        $this->linkReleaseWithPublisher($publisherId, $element->id);
                    }
                }
            }
        }

        if ($changed) {
            $element->persistElementData();
        }


        if (!empty($releaseInfo['inlayImages'])) {
            $this->importElementFiles($element, $releaseInfo['inlayImages'], 'inlayFilesSelector');
        }
        if (isset($releaseInfo['infoFiles'])) {
            $this->importElementFiles($element, $releaseInfo['infoFiles'], 'infoFilesSelector');
        }
        if (isset($releaseInfo['adFiles'])) {
            $this->importElementFiles($element, $releaseInfo['adFiles'], 'adFilesSelector');
        }

        $this->prodsDownloader->removeFile($releaseInfo['fileUrl']);
    }

    public function getReleasesByIdList($idList, $sort = [], $start = null, $amount = null)
    {
        $result = $this->loadReleases($idList, $sort, $start, $amount);

        return $result;
    }

    public function makeReleasesQuery()
    {
        return $this->db->table('module_zxrelease');
    }

    protected function loadReleases(Illuminate\Database\Query\Builder $query, $sort = [], $start = null, $amount = null)
    {
        if (is_array($sort)) {
            foreach ($sort as $property => &$order) {
                if (isset($this->releaseColumnRelations[$property])) {
                    $srcTableName = $this->db->getTablePrefix().$query->from;
                    foreach ($this->releaseColumnRelations[$property] as $criteria => $orderDirection) {
                        if ($criteria == 'dateCreated') {
                            $query->leftJoin('structure_elements', 'structure_elements.id', '=', $query->from.'.id');
                            $query->orderBy("structure_elements.dateCreated", $orderDirection);
                        } else {
                            if ($orderDirection === true) {
                                $query->orderByRaw("$srcTableName.$criteria $order");
                            } else {
                                if ($orderDirection === false) {
                                    if ($order == 'desc') {
                                        $query->orderByRaw("$srcTableName.$criteria asc");
                                    } else {
                                        $query->orderByRaw("$srcTableName.$criteria desc");
                                    }
                                } else {
                                    $query->orderByRaw("$srcTableName.$criteria $orderDirection");
                                }
                            }
                        }
                    }
                }
            }
        }

        $result = [];
        if ($start !== null) {
            $query->offset($start);
        }
        if ($amount !== null) {
            $query->limit($amount);
        }
        if ($records = $query->get()) {
            foreach ($records as $record) {
                if ($zxRelease = $this->manufactureElement($record['id'])) {
                    $this->elementsIndex[$zxRelease->id] = $zxRelease;
                    $result[] = $zxRelease;
                }
            }
        }

        return $result;
    }

    protected function sanitizeTitle($title)
    {
        $articles = ['The', 'La', 'El', 'A'];
        foreach ($articles as $article) {
            $search = ', ' . $article;
            if (mb_stripos($title, $search) !== false) {
                $title = $article . ' ' . mb_substr($title, 0, mb_strlen($search) * (-1));
            }
            $search = ',' . $article;
            if (mb_stripos($title, $search) !== false) {
                $title = $article . ' ' . mb_substr($title, 0, mb_strlen($search) * (-1));
            }
        }
        if (mb_substr($title, -2) == ' 1') {
            //$title = mb_substr($title, 0, -1);
        }

        return $title;
    }

    public function joinDeleteZxProd($mainZxProdId, $joinedZxProdId)
    {
        if ($joinedZxProdId == $mainZxProdId) {
            return false;
        }
        /**
         * @var zxProdElement $mainZxProd
         */
        if ($mainZxProd = $this->structureManager->getElementById($mainZxProdId)) {
            /**
             * @var zxProdElement $joinedZxProd
             */
            if ($joinedZxProd = $this->structureManager->getElementById($joinedZxProdId)) {
                if ($mainZxProd) {
                    $this->privilegesManager->copyPrivileges($joinedZxProd->id, $mainZxProdId);

                    //join releases, materials
                    if ($links = $this->linksManager->getElementsLinks($joinedZxProdId, null, 'parent')) {
                        foreach ($links as $link) {
                            $this->linksManager->unLinkElements($joinedZxProdId, $link->childStructureId, $link->type);
                            $this->linksManager->linkElements(
                                $mainZxProd->getId(),
                                $link->childStructureId,
                                $link->type
                            );
                        }
                    }
                    //join publishers, groups, categories
                    if ($links = $this->linksManager->getElementsLinks($joinedZxProdId, null, 'child')) {
                        foreach ($links as $link) {
                            $this->linksManager->unLinkElements($link->parentStructureId, $joinedZxProdId, $link->type);
                            $this->linksManager->linkElements(
                                $link->parentStructureId,
                                $mainZxProd->getId(),
                                $link->type
                            );
                        }
                    }

                    if (!$mainZxProd->party) {
                        $mainZxProd->party = $joinedZxProd->party;
                    }
                    if (!$mainZxProd->partyplace) {
                        $mainZxProd->partyplace = $joinedZxProd->partyplace;
                    }
                    if (!$mainZxProd->compo) {
                        $mainZxProd->compo = $joinedZxProd->compo;
                    }
                    if (!$mainZxProd->year) {
                        $mainZxProd->year = $joinedZxProd->year;
                    }
                    if (!$mainZxProd->youtubeId) {
                        $mainZxProd->youtubeId = $joinedZxProd->youtubeId;
                    }
                    if (!$mainZxProd->description) {
                        $mainZxProd->description = $joinedZxProd->description;
                    }
                    if (!$mainZxProd->legalStatus || $mainZxProd->legalStatus == 'unknown') {
                        $mainZxProd->legalStatus = $joinedZxProd->legalStatus;
                    }
                    if (!$mainZxProd->userId) {
                        $mainZxProd->userId = $joinedZxProd->userId;
                    }
                    if (!$mainZxProd->denyVoting) {
                        $mainZxProd->denyVoting = $joinedZxProd->denyVoting;
                    }
                    if (!$mainZxProd->denyComments) {
                        $mainZxProd->denyComments = $joinedZxProd->denyComments;
                    }
                    if (!$mainZxProd->language) {
                        $mainZxProd->language = $joinedZxProd->language;
                    }

                    $mainZxProd->persistElementData();
                    $mainZxProd->recalculate();

                    //take existing authors
                    $existingAuthorIds = [];
                    if ($existingAuthorShipRecords = $this->authorsManager->getElementAuthorsRecords($mainZxProdId)) {
                        foreach ($existingAuthorShipRecords as $record) {
                            $existingAuthorIds[] = $record['authorId'];
                        }
                    }

                    //delete duplicates from joined zxProd
                    if ($existingAuthorIds) {
                        $this->db->table('authorship')
                            ->where('elementId', '=', $joinedZxProdId)
                            ->whereIn('authorId', $existingAuthorIds)
                            ->delete();
                    }
                    //now move all non-duplicated author records to main zxProd
                    $this->db->table('authorship')
                        ->where('elementId', '=', $joinedZxProdId)
                        ->update(['elementId' => $mainZxProd->id]);

                    //move all import origin IDs to main prod
                    $this->db->table('import_origin')
                        ->where('elementId', '=', $joinedZxProdId)
                        ->update(['elementId' => $mainZxProd->id]);

                    $joinedZxProd->deleteElementData();
                }
            }
        }
        return true;
    }

    public function splitZxProd($prodId, $data)
    {
        $newProdElement = false;
        /**
         * @var zxProdElement $mainZxProd
         */
        if ($mainZxProd = $this->structureManager->getElementById($prodId)) {
            if ($firstParent = $mainZxProd->getFirstParentElement()) {
                if ($newProdElement = $this->structureManager->createElement('zxProd', 'show', $firstParent->id)) {
                    $newProdElement->persistElementData();
                    /*
                     * categories
                     */

                    if ($categoriesIds = $mainZxProd->getConnectedCategoriesIds()) {
                        foreach ($categoriesIds as $categoryId) {
                            $this->linksManager->linkElements($categoryId, $newProdElement->id, 'zxProdCategory');
                        }
                    }
                    foreach ($data['properties'] as $property => $value) {
                        $newProdElement->$property = $mainZxProd->$property;
                    }
                    $newProdElement->structureName = $newProdElement->title;
                    $newProdElement->persistElementData();

                    if (!empty($data['authors'])) {
                        $authorshipIds = array_keys($data['authors']);
                        $this->authorsManager->moveAuthorship($newProdElement->id, $authorshipIds);
                    }
                    if (!empty($data['groups'])) {
                        foreach ($data['groups'] as $id => $value) {
                            $this->linksManager->unLinkElements($id, $mainZxProd->id, 'zxProdGroups');
                            $this->linksManager->linkElements($id, $newProdElement->id, 'zxProdGroups');
                        }
                    }
                    if (!empty($data['publishers'])) {
                        foreach ($data['publishers'] as $id => $value) {
                            $this->linksManager->unLinkElements($id, $mainZxProd->id, 'zxProdPublishers');
                            $this->linksManager->linkElements($id, $newProdElement->id, 'zxProdPublishers');
                        }
                    }
                    if (!empty($data['releases'])) {
                        foreach ($data['releases'] as $id => $value) {
                            $this->linksManager->unLinkElements($mainZxProd->id, $id, 'structure');
                            $this->linksManager->linkElements($newProdElement->id, $id, 'structure');
                        }
                    }
                    if (!empty($data['screenshots'])) {
                        foreach ($data['screenshots'] as $id => $value) {
                            $this->linksManager->unLinkElements($mainZxProd->id, $id, 'connectedFile');
                            $this->linksManager->linkElements($newProdElement->id, $id, 'connectedFile');
                        }
                    }
                    if (!empty($data['links'])) {
                        foreach ($data['links'] as $string => $value) {
                            $parts = explode(';', $string);
                            if (($origin = $parts[0]) && ($importId = $parts[1])) {
                                $this->moveImportId(
                                    $mainZxProd->id,
                                    $newProdElement->id,
                                    $importId,
                                    $origin,
                                    'prod'
                                );
                            }
                        }
                    }
                    $this->structureManager->clearElementCache($mainZxProd->id);
                }
            }
        }
        return $newProdElement;
    }
}
