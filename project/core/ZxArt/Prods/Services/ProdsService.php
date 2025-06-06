<?php

namespace ZxArt\Prods\Services;

use ElementsManager;
use Exception;
use FilesElementTrait;
use Illuminate;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ImportIdOperatorTrait;
use LanguagesManager;
use linksManager;
use PathsManager;
use pressArticleElement;
use privilegesManager;
use ProdsDownloader;
use ReleaseFileTypesGatherer;
use ReleaseFormatsProvider;
use structureElement;
use structureManager;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Prods\ProdLabel;
use ZxArt\Import\Prods\ProdResolver;
use ZxArt\Parties\Services\PartiesService;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\ZxProdCategories\CategoryIds;
use ZxParsingManager;
use zxProdElement;
use zxReleaseElement;

class ProdsService extends ElementsManager
{
    protected const TABLE = ProdsRepository::TABLE;
    use ImportIdOperatorTrait;
    use ReleaseFormatsProvider;
    use ReleaseFileTypesGatherer;

    protected int $defaultCategoryId = CategoryIds::MISC->value;
    protected bool $forceUpdateYear = false;
    protected bool $forceUpdateYoutube = false;
    protected bool $forceUpdateExternalLink = false;
    protected bool $forceUpdateCategories = false;
    protected bool $forceUpdateImages = false;
    protected bool $forceUpdateTitles = false;
    protected bool $matchProdsWithoutYear = false;
    protected bool $forceUpdateAuthors = false;
    protected bool $forceUpdateGroups = false;
    protected bool $forceUpdatePublishers = false;
    protected bool $updateExistingProds = false;
    protected bool $updateExistingReleases = false;
    protected bool $forceUpdateReleaseAuthors = false;
    protected bool $forceUpdateReleasePublishers = false;
    protected bool $forceUpdateReleaseType = false;
    protected bool $forceUpdateReleaseFiles = false;
    protected bool $addImages = false;
    protected bool $resizeImages = false;

    protected array $columnRelations = [];
    protected array $releaseColumnRelations = [];

    public function setForceUpdateExternalLink(bool $forceUpdateExternalLink): void
    {
        $this->forceUpdateExternalLink = $forceUpdateExternalLink;
    }

    public function setMatchProdsWithoutYear(bool $matchProdsWithoutYear): void
    {
        $this->matchProdsWithoutYear = $matchProdsWithoutYear;
    }

    public function setForceUpdateReleaseFiles(bool $forceUpdateReleaseFiles): void
    {
        $this->forceUpdateReleaseFiles = $forceUpdateReleaseFiles;
    }

    public function setForceUpdateReleaseType(bool $forceUpdateReleaseType): void
    {
        $this->forceUpdateReleaseType = $forceUpdateReleaseType;
    }

    public function setForceUpdateReleaseAuthors(bool $forceUpdateReleaseAuthors): void
    {
        $this->forceUpdateReleaseAuthors = $forceUpdateReleaseAuthors;
    }

    public function setForceUpdateReleasePublishers(bool $forceUpdateReleasePublishers): void
    {
        $this->forceUpdateReleasePublishers = $forceUpdateReleasePublishers;
    }

    public function setUpdateExistingReleases(bool $updateExistingReleases): void
    {
        $this->updateExistingReleases = $updateExistingReleases;
    }

    public function setResizeImages(bool $resizeImages): void
    {
        $this->resizeImages = $resizeImages;
    }

    public function __construct(
        protected structureManager     $structureManager,
        protected PartiesService       $partiesService,
        protected GroupsService        $groupsService,
        protected ZxParsingManager     $zxParsingManager,
        protected AuthorsService       $authorsManager,
        protected linksManager         $linksManager,
        protected ProdsDownloader      $prodsDownloader,
        protected privilegesManager    $privilegesManager,
        protected PathsManager         $pathsManager,
        protected AuthorshipRepository $authorshipRepository,
        protected Connection           $db,
        protected LanguagesManager     $languagesManager,
        protected ProdResolver         $prodResolver,
    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
            'place' => ['if(partyplace,0,1), partyplace' => true],
            'date' => ['dateCreated' => true, 'id' => true],
            'year' => ['year' => true, 'dateAdded' => true, 'id' => true],
            'votes' => ['votes' => true, 'if(partyplace,0,1), partyplace' => false, 'title' => true],
        ];
        $this->releaseColumnRelations = [
            'title' => ['title' => true],
            'date' => ['dateCreated' => true, 'id' => true],
        ];
    }

    public function setForceUpdateImages(bool $forceUpdateImages): void
    {
        $this->forceUpdateImages = $forceUpdateImages;
    }

    /**
     * @param bool $updateExistingProds
     */
    public function setUpdateExistingProds($updateExistingProds): void
    {
        $this->updateExistingProds = $updateExistingProds;
    }

    /**
     * @param bool $forceUpdateYear
     */
    public function setForceUpdateYear($forceUpdateYear): void
    {
        $this->forceUpdateYear = $forceUpdateYear;
    }

    /**
     * @param bool $forceUpdateYoutube
     */
    public function setForceUpdateYoutube($forceUpdateYoutube): void
    {
        $this->forceUpdateYoutube = $forceUpdateYoutube;
    }

    /**
     * @param bool $forceUpdateGroups
     */
    public function setForceUpdateGroups($forceUpdateGroups): void
    {
        $this->forceUpdateGroups = $forceUpdateGroups;
    }

    /**
     * @param bool $forceUpdatePublishers
     */
    public function setForceUpdatePublishers($forceUpdatePublishers): void
    {
        $this->forceUpdatePublishers = $forceUpdatePublishers;
    }

    /**
     * @param bool $forceUpdateAuthors
     */
    public function setForceUpdateAuthors($forceUpdateAuthors): void
    {
        $this->forceUpdateAuthors = $forceUpdateAuthors;
    }

    public function setPathsManager(PathsManager $pathsManager): void
    {
        $this->pathsManager = $pathsManager;
    }

    /**
     * @param bool $forceUpdateCategories
     */
    public function setForceUpdateCategories($forceUpdateCategories): void
    {
        $this->forceUpdateCategories = $forceUpdateCategories;
    }

    /**
     * @param bool $addImages
     */
    public function setAddImages($addImages): void
    {
        $this->addImages = $addImages;
    }

    /**
     * @param bool $forceUpdateTitles
     */
    public function setForceUpdateTitles($forceUpdateTitles): void
    {
        $this->forceUpdateTitles = $forceUpdateTitles;
    }

    public function importProd(array $prodInfo, string $origin): ?zxProdElement
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
            $prod = new ProdLabel(
                id: $prodInfo['id'],
                title: $prodInfo['title'],
                year: $prodInfo['year'] ?? null,
                authorRoles: $prodInfo['authors'] ?? [],
            );
            if ($element = $this->prodResolver->resolve($prod, $this->matchProdsWithoutYear)) {
                $this->saveImportId($element->id, $prodId, $origin, 'prod');
            }
        }

        if (!$element) {
            $element = $this->createProd($prodInfo, $origin);
        }

        if ($element) {
            if ($this->updateExistingProds) {
                $element = $this->updateProd($element, $prodInfo, $origin);
            }
            if (!empty($prodInfo['releases'])) {
                foreach ($prodInfo['releases'] as $releaseInfo) {
                    $this->importRelease($releaseInfo, $prodInfo['id'], $origin);
                }
            }
        }

        return $element;
    }

    protected function createProd(array $prodInfo, string $origin): ?zxProdElement
    {
        $category = null;
        if (!empty($prodInfo['directCategories'])) {
            $category = reset($prodInfo['directCategories']);
        }
        if (!$category) {
            $category = $this->defaultCategoryId;
        }
        /**
         * @var zxProdElement $element
         */
        if ($element = $this->structureManager->createElement('zxProd', 'show', $category, false, 'zxProdCategory')) {
            $element->dateAdded = time();
            $this->saveImportId($element->getId(), $prodInfo['id'], $origin, 'prod');
            $this->updateProd($element, $prodInfo, $origin, true);
        }

        return $element;
    }

    protected function importLabelsInfo($infoIndex, $origin): void
    {
        $infoIndex = array_reverse($infoIndex);
        foreach ($infoIndex as $gatheredInfo) {
            if ($gatheredInfo['isAlias'] && $gatheredInfo['isPerson']) {
                $this->authorsManager->importAuthorAlias($gatheredInfo, $origin);
            } elseif ($gatheredInfo['isGroup']) {
                $this->groupsService->importGroup($gatheredInfo, $origin);
            } elseif ($gatheredInfo['isPerson']) {
                $this->authorsManager->importAuthor($gatheredInfo, $origin);
            } else {
                //we don't know anything about this label. lets search for any group with that name
                $result = $this->groupsService->importGroup($gatheredInfo, $origin, false);
                if (!$result) {
                    //search for author alias with that name
                    $result = $this->authorsManager->importAuthorAlias($gatheredInfo, $origin, false);
                }
                if (!$result) {
                    //just create author by default.
                    $this->authorsManager->importAuthor($gatheredInfo, $origin);
                }
            }
        }
    }

    protected function updateProd(zxProdElement $element, array $prodInfo, $origin, bool $justCreated = false): zxProdElement
    {
        $changed = false;
        if (!empty($prodInfo['title']) && ($element->title != $prodInfo['title'])) {
            if (!$element->title || $this->forceUpdateTitles) {
                $changed = true;
                $element->title = $prodInfo['title'];
                $element->structureName = $prodInfo['title'];
            }
        }
        if (!empty($prodInfo['altTitle']) && ($element->altTitle != $prodInfo['altTitle'])) {
            $changed = true;
            $element->altTitle = $prodInfo['altTitle'];
        }
        if (
            !empty($prodInfo['legalStatus']) &&
            (empty($element->legalStatus) || $justCreated) &&
            $element->legalStatus != $prodInfo['legalStatus']
        ) {
            $changed = true;
            $element->legalStatus = $prodInfo['legalStatus'];
        }
        if (!empty($prodInfo['year']) && (($element->year != $prodInfo['year']) && (!$element->year || $this->forceUpdateYear || $justCreated))) {
            $changed = true;
            $element->year = $prodInfo['year'];
        }
        if (!empty($prodInfo['compo']) && ($element->compo != $prodInfo['compo'])) {
            $changed = true;
            $element->compo = $prodInfo['compo'];
        }
        if (!empty($prodInfo['description']) && !$element->description) {
            $changed = true;
            $element->description = $prodInfo['description'];
        }
        if (!empty($prodInfo['party']) && (!$element->party || (!empty($prodInfo['party']['place']) && !$element->partyplace))) {
            $partyTitle = $prodInfo['party']['title'] ?? null;
            $partyYear = $prodInfo['party']['year'] ?? null;
            if ($partyTitle && $partyYear) {
                if ($partyElement = $this->partiesService->getPartyByTitleAndYear($partyTitle, $partyYear)) {
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
        if (!empty($prodInfo['labels']) && ($this->forceUpdatePublishers || $this->forceUpdateGroups || $this->forceUpdateAuthors || $justCreated)) {
            $this->importLabelsInfo($prodInfo['labels'], $origin);
        }
        if (!empty($prodInfo['directCategories'])) {
            if ($this->forceUpdateCategories || $justCreated || !$element->getConnectedCategoriesIds()) {
                foreach ($prodInfo['directCategories'] as $categoryId) {
                    $this->linksManager->linkElements($categoryId, $element->id, 'zxProdCategory');
                }
            }
        }

        if (!empty($prodInfo['undetermined'])) {
            foreach ($prodInfo['undetermined'] as $undeterminedId => $roles) {
                if ($elementId = $this->getElementIdByImportId($undeterminedId, $origin, 'group')) {
                    $prodInfo['groupsIds'][] = $undeterminedId;
                } elseif ($elementId = $this->getElementIdByImportId($undeterminedId, $origin, 'author')) {
                    $prodInfo['authors'][$undeterminedId] = $roles;
                }
            }
        }

        $authorsInfo = $element->getAuthorsInfo('prod');
        if (($this->forceUpdateAuthors || $justCreated || !$authorsInfo) && !empty($prodInfo['authors'])) {
            foreach ($prodInfo['authors'] as $importAuthorId => $roles) {
                if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                    $this->authorshipRepository->addAuthorship($element->id, $authorId, 'prod', $roles);
                }
            }
        }

        if (!empty($prodInfo['groups']) && (!$element->groups || $this->forceUpdateGroups)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdGroups', 'child');
            foreach ($prodInfo['groups'] as $importGroupId) {
                if ($groupId = $this->getElementIdByImportId($importGroupId, $origin, 'group')) {
                    if (!isset($linksIndex[$groupId])) {
                        $this->linksManager->linkElements($groupId, $element->id, 'zxProdGroups');
                    }
                    unset($linksIndex[$groupId]);
                }
            }
            foreach ($linksIndex as $key => $link) {
                $link->delete();
            }
        }
        if (!empty($prodInfo['publishers']) && (!$element->publishers || $this->forceUpdatePublishers)) {
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
            foreach ($linksIndex as $key => $link) {
                $link->delete();
            }
        }
        if (!empty($prodInfo['compilationItems'])) {
            if (!$element->compilationItems) {
                foreach ($prodInfo['compilationItems'] as $importItemId) {
                    if ($prodId = $this->getElementIdByImportId($importItemId, $origin, 'prod')) {
                        $this->linksManager->linkElements($element->id, $prodId, 'compilation');
                    } elseif ($releaseId = $this->getElementIdByImportId($importItemId, $origin, 'release')) {
                        $this->linksManager->linkElements($element->id, $releaseId, 'compilation');
                    }
                }
            }
        }

        if (!empty($prodInfo['seriesProds'])) {
            if (!$element->seriesProds) {
                foreach ($prodInfo['seriesProds'] as $importItemId) {
                    if ($prodId = $this->getElementIdByImportId($importItemId, $origin, 'prod')) {
                        $this->linksManager->linkElements($element->id, $prodId, 'series');
                    }
                }
            }
        }
        if (!empty($prodInfo['articles'])) {
            if (!$element->articles) {
                foreach ($prodInfo['articles'] as $articleData) {
                    /**
                     * @var pressArticleElement $articleElement
                     */
                    if ($articleElement = $this->structureManager->createElement(
                        'pressArticle',
                        'showForm',
                        $element->getId(),
                        false,
                        'prodArticle'
                    )) {
                        $articleElement->title = $articleData['title'];
                        $articleElement->structureName = $articleElement->title;
                        $articleElement->introduction = $articleData['introduction'];
                        $articleElement->externalLink = $articleData['externalLink'];
                        $articleElement->content = $articleData['content'];
                        $articleElement->persistElementData();
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

        if (!empty($prodInfo['maps']) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('mapFilesSelector'))) {
            $propertyName = 'mapFilesSelector';
            $existingFiles = $element->getFilesList($propertyName);
            foreach ($prodInfo['maps'] as $map) {
                try {
                    $this->importElementFile($element, $map['url'], $existingFiles, $map['author'], $propertyName);
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }

        if (!empty($prodInfo['inlayImages']) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('inlayFilesSelector'))) {
            $this->importElementFiles($element, $prodInfo['inlayImages'], 'inlayFilesSelector');
        }

        if (!empty($prodInfo['rzx'])) {
            $propertyName = 'rzx';
            $existingFiles = $element->getFilesList($propertyName);
            foreach ($prodInfo['rzx'] as $rzx) {
                try {
                    $this->importElementFile($element, $rzx['url'], $existingFiles, $rzx['author'], 'rzx');
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }

        if (!empty($prodInfo['importIds'])) {
            foreach ($prodInfo['importIds'] as $importOrigin => $id) {
                if (!$this->getElementIdByImportId($id, $importOrigin, 'prod')) {
                    $this->saveImportId($element->getId(), $id, $importOrigin, 'prod');
                }
            }
        }

        return $element;
    }

    /**
     * @throws ReleaseDownloadException
     */
    private function importElementFile(zxReleaseElement|zxProdElement $element, string $fileUrl, array $existingFiles, string $fileAuthor = '', string $propertyName = 'connectedFile'): void
    {
        $this->structureManager->setNewElementLinkType($element->getConnectedFileType($propertyName));
        $uploadsPath = $this->pathsManager->getPath('uploads');

        $originalFileName = basename($fileUrl);
        $fileExists = false;
        foreach ($existingFiles as $existingFile) {
            if ($originalFileName === urldecode($existingFile->fileName)) {
                $size = filesize($uploadsPath . $existingFile->fileName);
                if ($size > 0) {
                    $fileExists = true;
                    break;
                }
            }
        }

        if (!$fileExists) {
            $filePath = $uploadsPath . $originalFileName;
            $downloaded = $this->prodsDownloader->downloadUrl($fileUrl, $filePath);
            if (!$downloaded) {
                sleep(10);
                $downloaded = $this->prodsDownloader->downloadUrl($fileUrl, $filePath);
            }
            if (!$downloaded) {
                sleep(20);
                $downloaded = $this->prodsDownloader->downloadUrl($fileUrl, $filePath);
            }
            if (!$downloaded) {
                throw new ReleaseDownloadException('Unable to download release ' . $element->id . ' ' . $fileUrl);
            }
            if ($filePath && ($fileElement = $this->structureManager->createElement(
                    'file',
                    'showForm',
                    $element->getId()
                ))) {
                $destinationFolder = $element->getUploadedFilesPath($propertyName);

                $info = pathinfo($fileUrl);
                if (empty($info['extension'])) {
                    if ($mimeType = mime_content_type($filePath)) {
                        $mimeTypes = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'png',
                            'application/pdf' => 'pdf',
                        ];

                        $extension = isset($mimeTypes[$mimeType]) ? $mimeTypes[$mimeType] : null;
                        if ($extension) {
                            $originalFileName .= '.' . $extension;
                        }
                    }
                }
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

        $this->structureManager->setNewElementLinkType();

    }

    /**
     * @param FilesElementTrait $element
     * @param string $propertyName
     * @throws Exception
     */
    protected function importElementFiles($element, $images, $propertyName = 'connectedFile'): void
    {
        $existingFiles = $element->getFilesList($propertyName);
        if (!$existingFiles || $this->addImages) {
            foreach ($images as $imageUrl) {
                try {
                    $this->importElementFile($element, $imageUrl, $existingFiles, '', $propertyName);
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }
    }

    protected function linkReleaseWithAuthor($authorId, int $prodId, $roles = []): void
    {
        $this->authorshipRepository->addAuthorship($prodId, $authorId, 'release', $roles);
    }

    protected function linkReleaseWithPublisher($publisherId, int $prodId): void
    {
        $this->linksManager->linkElements($publisherId, $prodId, 'zxReleasePublishers');
    }

    /**
     * @throws Exception
     */
    protected function getProdByReleaseMd5($prodInfo): ?zxProdElement
    {
        if (!empty($prodInfo['releases'])) {
            foreach ($prodInfo['releases'] as $releaseInfo) {
                try {
                    if ($releaseElement = $this->getReleaseByMd5($releaseInfo, $prodInfo['id'])) {
                        return $releaseElement->getProd();
                    }
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }
        return null;
    }

    /**
     * @throws ReleaseDownloadException
     */
    private function getReleaseByMd5($releaseInfo, $prodId): ?zxReleaseElement
    {
        if (!$releaseInfo['fileUrl']) {
            return null;
        }
        if (empty($releaseInfo['md5'])) {
            $path = $this->prodsDownloader->getDownloadedPath($releaseInfo['fileUrl']);
            if (!$path) {
                sleep(10);
                $path = $this->prodsDownloader->getDownloadedPath($releaseInfo['fileUrl']);
            }
            if (!$path) {
                throw new ReleaseDownloadException('Unable to download release ' . $prodId . ' ' . $releaseInfo['id'] . ' ' . $releaseInfo['title'] . ' ' . $releaseInfo['fileUrl']);
            }
            if ($path) {
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
                            if (count($index[$elementId]) === count($releaseFiles)) {
                                $foundReleaseId = $elementId;
                                break;
                            }
                        }
                        return $this->structureManager->getElementById($foundReleaseId);
                    }
                }
            }
        }
        return null;
    }


    public function importRelease($releaseInfo, $prodId, $origin): bool|zxReleaseElement
    {
        $releaseId = $releaseInfo['id'];
        $releaseInfo['title'] = $this->sanitizeTitle($releaseInfo['title']);
        /**
         * @var zxReleaseElement $element
         */
        $element = $this->getElementByImportId($releaseId, $origin, 'release');
        if (!$element) {
            try {
                if ($element = $this->getReleaseByMd5($releaseInfo, $prodId)) {
                    $this->saveImportId($element->id, $releaseId, $origin, 'release');
                }
            } catch (ReleaseDownloadException $e) {
                $this->logError($e->getMessage());
            }
        }
        if (!$element) {
            return $this->createRelease($releaseInfo, $prodId, $origin);
        }
        if ($element && $this->updateExistingReleases) {
            $this->updateRelease($element, $releaseInfo, $origin);
        }
        return $element;
    }

    /**
     * @param array $releaseInfo
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
                $this->updateRelease($element, $releaseInfo, $origin, true);
            }
        }

        return $element;
    }

    /**
     * @param zxReleaseElement $element
     */
    protected function updateRelease($element, array $releaseInfo, $origin, bool $justCreated = false): void
    {
        $changed = false;
        if (($this->forceUpdateTitles || !$element->title) && !empty($releaseInfo['title'])) {
            $element->title = $releaseInfo['title'];
            $element->structureName = $releaseInfo['title'];
            $changed = true;
        }
        if ((!$element->year || $this->forceUpdateYear) && !empty($releaseInfo['year'])) {
            $element->year = $releaseInfo['year'];
            $changed = true;
        }
        if (!$element->hardwareRequired && !empty($releaseInfo['hardwareRequired'])) {
            $element->hardwareRequired = array_unique($releaseInfo['hardwareRequired']);
            $changed = true;
        }
        if (($this->forceUpdateReleaseType || $justCreated) && (!$element->releaseType || $element->releaseType === 'unknown') && !empty($releaseInfo['releaseType'])) {
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
        if (($this->forceUpdateReleaseFiles || $justCreated)) {
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
                if (!empty($releaseInfo['fileName'])) {
                    $fileName = $releaseInfo['fileName'];
                } else {
                    $fileName = $info['filename'] . '.' . $info['extension'];
                }

                if ($element->fileName != $fileName || !is_file($element->getFilePath())) {
                    $changed = true;

                    $element->file = $element->getId();
                    $element->fileName = $fileName;
                    $element->parsed = 0;

                    $this->prodsDownloader->moveFileContents($element->getFilePath(), $releaseInfo['fileUrl']);
                }
            }
        }

        if (!empty($releaseInfo['description']) && !$element->description) {
            $element->description = $releaseInfo['description'];
            $changed = true;
        }

        if (($this->forceUpdateReleasePublishers || $this->forceUpdateReleaseAuthors || $justCreated) && !empty($releaseInfo['labels'])) {
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

        if (($this->forceUpdateReleaseAuthors || $justCreated) && !empty($releaseInfo['authors'])) {
            if (!$element->getAuthorsInfo('release')) {
                foreach ($releaseInfo['authors'] as $importAuthorId => $roles) {
                    if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                        $this->linkReleaseWithAuthor($authorId, $element->id, $roles);
                    }
                }
            }
        }
        if (($this->forceUpdateReleasePublishers || $justCreated) && !empty($releaseInfo['publishers'])) {
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

        if (!empty($releaseInfo['images']) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('screenshotsSelector'))) {
            $this->importElementFiles($element, $releaseInfo['images'], 'screenshotsSelector');
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

    public function getReleasesByIdList(Builder|null $idList, array|null $sort = [], int|null $start = null, int|null $amount = null)
    {
        $result = $this->loadReleases($idList, $sort, $start, $amount);

        return $result;
    }

    public function makeReleasesQuery(): Builder
    {
        return $this->db->table('module_zxrelease');
    }

    /**
     * @psalm-return list{0?: mixed,...}
     */
    protected function loadReleases(Builder $query, array|null $sort = [], int|null $start = null, int|null $amount = null): array
    {
        if (is_array($sort)) {
            foreach ($sort as $property => $order) {
                if (isset($this->releaseColumnRelations[$property])) {
                    $srcTableName = $this->db->getTablePrefix() . $query->from;
                    foreach ($this->releaseColumnRelations[$property] as $criteria => $orderDirection) {
                        if ($criteria == 'dateCreated') {
                            $query->leftJoin('structure_elements', 'structure_elements.id', '=', $query->from . '.id');
                            $query->orderBy("structure_elements.dateCreated", $orderDirection);
                        } else {
                            $orderColumn = $criteria === 'title' ? "LOWER($srcTableName.$criteria)" : "$srcTableName.$criteria";

                            if ($orderDirection === true) {
                                $query->orderByRaw("$orderColumn $order");
                            } else {
                                if ($orderDirection === false) {
                                    if ($order == 'desc') {
                                        $query->orderByRaw("$orderColumn asc");
                                    } else {
                                        $query->orderByRaw("$orderColumn desc");
                                    }
                                } else {
                                    $query->orderByRaw("$orderColumn $orderDirection");
                                }
                            }
                        }
                    }
                }
            }
        }

        $result = [];
        if ($start !== null && $start > 0) {
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

    public function joinDeleteZxProd(int $mainZxProdId, int $joinedZxProdId, bool $releasesOnly = false): bool
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
                    if (!$releasesOnly) {
                        $this->privilegesManager->copyPrivileges($joinedZxProd->id, $mainZxProdId);
                    }
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
                    if (!$releasesOnly) {
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
                    }

                    $mainZxProd->persistElementData();
                    $mainZxProd->recalculate();
                    if (!$releasesOnly) {
                        //take existing authors
                        $existingAuthorIds = [];
                        if ($existingAuthorShipRecords = $this->authorshipRepository->getElementAuthorsRecords($mainZxProdId)) {
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
                    }
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

    public function splitZxProd(int $prodId, array $data): bool|structureElement
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
                        $this->authorshipRepository->moveAuthorship($newProdElement->id, $authorshipIds);
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

    public function copyAuthorship($sourceElement, $targetElement): void
    {
        $sourceElementId = $sourceElement->id;
        $targetElementId = $targetElement->id;
        if ($existingAuthorShipRecords = $this->authorshipRepository->getElementAuthorsRecords($sourceElementId)) {
            foreach ($existingAuthorShipRecords as $record) {
                $this->authorshipRepository->saveAuthorship(
                    $targetElementId,
                    $record['authorId'],
                    $record['type'],
                    $record['roles'],
                );
            }
        }
    }
}
