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
use privilegesManager;
use ProdsDownloader;
use ReleaseFileTypesGatherer;
use ReleaseFormatsProvider;
use structureElement;
use structureManager;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\FileParsing\ZxParsingManager;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Prods\ProdLabel;
use ZxArt\Import\Prods\ProdResolver;
use ZxArt\Parties\Services\PartiesService;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\ZxProdCategories\CategoryIds;
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
        return $this->importProdDTO(ProdImportDTO::fromArray($prodInfo), $origin);
    }

    public function importProdDTO(ProdImportDTO $prod, string $origin): ?zxProdElement
    {
        $prodId = $prod->id;
        $sanitizedTitle = $this->sanitizeTitle($prod->title);

        $element = $this->getElementByImportId($prodId, $origin, 'prod');

        if (!$element && $prod->ids) {
            foreach ($prod->ids as $idOrigin => $id) {
                if ($element = $this->getElementByImportId($id, $idOrigin, 'prod')) {
                    $this->saveImportId($element->id, $prodId, $origin, 'prod');
                    break;
                }
            }
        }

        if (!$element) {
            if ($candidate = $this->getProdByReleaseMd5DTO($prod)) {
                $element = $candidate;
                $this->saveImportId($element->id, $prodId, $origin, 'prod');
            }
        }

        if (!$element) {
            $label = new ProdLabel(
                id: $prod->id,
                title: $sanitizedTitle,
                year: $prod->year,
                authorRoles: $prod->authors
            );
            if ($resolved = $this->prodResolver->resolve($label, $this->matchProdsWithoutYear)) {
                $element = $resolved;
                $this->saveImportId($element->id, $prodId, $origin, 'prod');
            }
        }

        if (!$element) {
            $element = $this->createProdDTO(
                new ProdImportDTO(
                    id: $prod->id,
                    title: $sanitizedTitle,
                    altTitle: $prod->altTitle,
                    description: $prod->description,
                    language: $prod->language,
                    legalStatus: $prod->legalStatus,
                    youtubeId: $prod->youtubeId,
                    externalLink: $prod->externalLink,
                    compo: $prod->compo,
                    year: $prod->year,
                    ids: $prod->ids,
                    importIds: $prod->importIds,
                    labels: $prod->labels,
                    authors: $prod->authors,
                    groups: $prod->groups,
                    publishers: $prod->publishers,
                    undetermined: $prod->undetermined,
                    party: $prod->party,
                    directCategories: $prod->directCategories,
                    categories: $prod->categories,
                    images: $prod->images,
                    maps: $prod->maps,
                    inlayImages: $prod->inlayImages,
                    rzx: $prod->rzx,
                    compilationItems: $prod->compilationItems,
                    seriesProds: $prod->seriesProds,
                    articles: $prod->articles,
                    releases: $prod->releases,
                ),
                $origin
            );
        }

        if ($element && $this->updateExistingProds) {
            $element = $this->updateProdDTO($element, $prod, $origin);
        }

        if ($element && $prod->releases) {
            foreach ($prod->releases as $release) {
                $this->importReleaseDTO($release, $prod->id, $origin);
            }
        }

        return $element;
    }

    protected function createProd(array $prodInfo, string $origin): ?zxProdElement
    {
        return $this->createProdDTO(ProdImportDTO::fromArray($prodInfo), $origin);
    }

    protected function createProdDTO(ProdImportDTO $prod, string $origin): ?zxProdElement
    {
        $category = null;
        if (!empty($prod->directCategories)) {
            $category = $prod->directCategories[0];
        }
        if (!$category) {
            $category = $this->defaultCategoryId;
        }

        $element = $this->structureManager->createElement('zxProd', 'show', $category, false, 'zxProdCategory');
        if ($element instanceof zxProdElement) {
            $element->dateAdded = time();
            $this->saveImportId($element->getId(), $prod->id, $origin, 'prod');
            $this->updateProdDTO($element, $prod, $origin, true);
            return $element;
        }
        return null;
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
        return $this->updateProdDTO($element, ProdImportDTO::fromArray($prodInfo), (string)$origin, $justCreated);
    }

    protected function updateProdDTO(zxProdElement $element, ProdImportDTO $prod, string $origin, bool $justCreated = false): zxProdElement
    {
        $changed = false;

        if (!empty($prod->title) && ($element->title != $prod->title)) {
            if (!$element->title || $this->forceUpdateTitles) {
                $element->title = $prod->title;
                $element->structureName = $prod->title;
                $changed = true;
            }
        }
        if (!empty($prod->altTitle) && ($element->altTitle != $prod->altTitle)) {
            $element->altTitle = $prod->altTitle;
            $changed = true;
        }
        if (!empty($prod->legalStatus) && (empty($element->legalStatus) || $justCreated) && $element->legalStatus != $prod->legalStatus) {
            $element->legalStatus = $prod->legalStatus;
            $changed = true;
        }
        if (!empty($prod->year) && (($element->year != $prod->year) && (!$element->year || $this->forceUpdateYear || $justCreated))) {
            $element->year = $prod->year;
            $changed = true;
        }
        if (!empty($prod->compo) && ($element->compo != $prod->compo)) {
            $element->compo = $prod->compo;
            $changed = true;
        }
        if (!empty($prod->description) && !$element->description) {
            $element->description = $prod->description;
            $changed = true;
        }
        if (!empty($prod->party) && (!$element->party || (!empty($prod->party->place) && !$element->partyplace))) {
            $partyTitle = $prod->party->title ?? null;
            $partyYear = $prod->party->year ?? null;
            if ($partyTitle && $partyYear) {
                if ($partyElement = $this->partiesService->getPartyByTitleAndYear($partyTitle, $partyYear)) {
                    if ($element->party != $partyElement->id) {
                        $element->party = $partyElement->id;
                        $element->renewPartyLink();
                        $changed = true;
                    }
                    if (!empty($prod->party->place)) {
                        if ($element->partyplace != $prod->party->place) {
                            $element->partyplace = $prod->party->place;
                            $changed = true;
                        }
                    }
                }
            }
        }
        if (!empty($prod->youtubeId) && ($element->youtubeId != $prod->youtubeId) && ($this->forceUpdateYoutube || $justCreated)) {
            $element->youtubeId = $prod->youtubeId;
            $changed = true;
        }
        if (!empty($prod->externalLink) && (($element->externalLink != $prod->externalLink) && ($this->forceUpdateExternalLink || $justCreated))) {
            $element->externalLink = $prod->externalLink;
            $changed = true;
        }
        if (!empty($prod->language) && $element->language != $prod->language) {
            $element->language = $prod->language;
            $changed = true;
        }

        if ($changed) {
            $element->persistElementData();
        }

        if (!empty($prod->labels) && ($this->forceUpdatePublishers || $this->forceUpdateGroups || $this->forceUpdateAuthors || $justCreated)) {
            $this->importLabelsInfo(array_map(static fn($l) => $l->toArray(), $prod->labels), $origin);
        }

        if (!empty($prod->directCategories)) {
            if ($this->forceUpdateCategories || $justCreated || !$element->getConnectedCategoriesIds()) {
                foreach ($prod->directCategories as $categoryId) {
                    $this->linksManager->linkElements($categoryId, $element->id, 'zxProdCategory');
                }
            }
        }

        if (!empty($prod->undetermined)) {
            foreach ($prod->undetermined as $undeterminedId => $roles) {
                if ($this->getElementIdByImportId($undeterminedId, $origin, 'group')) {
                    // noop
                } elseif ($authorId = $this->getElementIdByImportId($undeterminedId, $origin, 'author')) {
                    $this->authorshipRepository->addAuthorship($element->id, $authorId, 'prod', $roles);
                }
            }
        }

        $authorsInfo = $element->getAuthorsInfo('prod');
        if (($this->forceUpdateAuthors || $justCreated || !$authorsInfo) && !empty($prod->authors)) {
            foreach ($prod->authors as $importAuthorId => $roles) {
                if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                    $this->authorshipRepository->addAuthorship($element->id, $authorId, 'prod', $roles);
                }
            }
        }

        if (!empty($prod->groups) && (!$element->groups || $this->forceUpdateGroups)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdGroups', 'child');
            foreach ($prod->groups as $importGroupId) {
                if ($groupId = $this->getElementIdByImportId($importGroupId, $origin, 'group')) {
                    if (!isset($linksIndex[$groupId])) {
                        $this->linksManager->linkElements($groupId, $element->id, 'zxProdGroups');
                    }
                    unset($linksIndex[$groupId]);
                }
            }
            foreach ($linksIndex as $link) {
                $link->delete();
            }
        }

        if (!empty($prod->publishers) && (!$element->publishers || $this->forceUpdatePublishers)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdPublishers', 'child');
            foreach ($prod->publishers as $importPublisherId) {
                $publisherId = $this->getElementIdByImportId($importPublisherId, $origin, 'group')
                    ?: $this->getElementIdByImportId($importPublisherId, $origin, 'author');
                if ($publisherId) {
                    if (!isset($linksIndex[$publisherId])) {
                        $this->linksManager->linkElements($publisherId, $element->id, 'zxProdPublishers');
                    }
                    unset($linksIndex[$publisherId]);
                }
            }
            foreach ($linksIndex as $link) {
                $link->delete();
            }
        }

        if (!empty($prod->compilationItems)) {
            if (!$element->compilationItems) {
                foreach ($prod->compilationItems as $importItemId) {
                    if ($prodId = $this->getElementIdByImportId($importItemId, $origin, 'prod')) {
                        $this->linksManager->linkElements($element->id, $prodId, 'compilation');
                    } elseif ($releaseId = $this->getElementIdByImportId($importItemId, $origin, 'release')) {
                        $this->linksManager->linkElements($element->id, $releaseId, 'compilation');
                    }
                }
            }
        }

        if (!empty($prod->seriesProds)) {
            if (!$element->seriesProds) {
                foreach ($prod->seriesProds as $importItemId) {
                    if ($prodId = $this->getElementIdByImportId($importItemId, $origin, 'prod')) {
                        $this->linksManager->linkElements($element->id, $prodId, 'series');
                    }
                }
            }
        }

        if (!empty($prod->articles)) {
            if (!$element->articles) {
                foreach ($prod->articles as $article) {
                    if ($articleElement = $this->structureManager->createElement(
                        'pressArticle',
                        'showForm',
                        $element->getId(),
                        false,
                        'prodArticle'
                    )) {
                        $articleElement->title = $article->title;
                        $articleElement->structureName = $article->title;
                        $articleElement->introduction = $article->introduction;
                        $articleElement->externalLink = $article->externalLink;
                        $articleElement->content = $article->content;
                        $articleElement->persistElementData();
                    }
                }
            }
        }

        if (!empty($prod->categories) && (!$element->getConnectedCategoriesIds() || $this->forceUpdateCategories || $justCreated)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdCategory', 'child');
            foreach ($prod->categories as $importCategoryId) {
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

        if (!empty($prod->images) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('connectedFile'))) {
            $this->importElementFiles($element, $prod->images);
            if ($this->resizeImages) {
                $element->resizeImages();
            }
        }

        if (!empty($prod->maps) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('mapFilesSelector'))) {
            $propertyName = 'mapFilesSelector';
            $existingFiles = $element->getFilesList($propertyName);
            foreach ($prod->maps as $map) {
                try {
                    $this->importElementFile($element, $map->url, $existingFiles, $map->author, $propertyName);
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }

        if (!empty($prod->inlayImages) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('inlayFilesSelector'))) {
            $this->importElementFiles($element, $prod->inlayImages, 'inlayFilesSelector');
        }

        if (!empty($prod->rzx)) {
            $propertyName = 'rzx';
            $existingFiles = $element->getFilesList($propertyName);
            foreach ($prod->rzx as $rzx) {
                try {
                    $this->importElementFile($element, $rzx->url, $existingFiles, $rzx->author, $propertyName);
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }

        if (!empty($prod->importIds)) {
            foreach ($prod->importIds as $importOrigin => $id) {
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
                $path = $existingFile->getFilePath();
                $size = filesize($path);
                if (is_file($path) && $size > 0) {
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
                /**
                 * @var fileElement $fileElement
                 */
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


    protected function getProdByReleaseMd5DTO(ProdImportDTO $prod): ?zxProdElement
    {
        if (!empty($prod->releases)) {
            foreach ($prod->releases as $release) {
                try {
                    if ($releaseElement = $this->getReleaseByMd5DTO($release, $prod->id)) {
                        return $releaseElement->getProd();
                    }
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }
        return null;
    }

    private function getReleaseByMd5DTO(ReleaseImportDTO $release, string $prodId): ?zxReleaseElement
    {
        if (!$release->fileUrl) {
            return null;
        }
        if (empty($release->md5)) {
            $path = $this->prodsDownloader->getDownloadedPath($release->fileUrl);
            if (!$path) {
                sleep(10);
                $path = $this->prodsDownloader->getDownloadedPath($release->fileUrl);
            }
            if (!$path) {
                throw new ReleaseDownloadException('Unable to download release ' . $prodId . ' ' . $release->id . ' ' . $release->title . ' ' . $release->fileUrl);
            }
            if ($path) {
                if ($structure = $this->zxParsingManager->parseFileStructure($path)) {
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
                        return $foundReleaseId ? $this->structureManager->getElementById($foundReleaseId) : null;
                    }
                }
            }
        }
        return null;
    }


    public function importRelease($releaseInfo, $prodId, $origin): bool|zxReleaseElement
    {
        return $this->importReleaseDTO(ReleaseImportDTO::fromArray($releaseInfo), (string)$prodId, (string)$origin);
    }

    public function importReleaseDTO(ReleaseImportDTO $release, string $prodId, string $origin): bool|zxReleaseElement
    {
        $releaseId = $release->id;
        $sanitizedTitle = $this->sanitizeTitle($release->title);

        $element = $this->getElementByImportId($releaseId, $origin, 'release');
        if (!$element) {
            try {
                if ($candidate = $this->getReleaseByMd5DTO($release, $prodId)) {
                    $element = $candidate;
                    $this->saveImportId($element->id, $releaseId, $origin, 'release');
                }
            } catch (ReleaseDownloadException $e) {
                $this->logError($e->getMessage());
            }
        }
        if (!$element) {
            $element = $this->createReleaseDTO(
                new ReleaseImportDTO(
                    id: $release->id,
                    title: $sanitizedTitle,
                    year: $release->year,
                    language: $release->language,
                    version: $release->version,
                    releaseType: $release->releaseType,
                    filePath: $release->filePath,
                    fileUrl: $release->fileUrl,
                    fileName: $release->fileName,
                    description: $release->description,
                    hardwareRequired: $release->hardwareRequired,
                    labels: $release->labels,
                    authors: $release->authors,
                    publishers: $release->publishers,
                    undetermined: $release->undetermined,
                    images: $release->images,
                    inlayImages: $release->inlayImages,
                    infoFiles: $release->infoFiles,
                    adFiles: $release->adFiles,
                    md5: $release->md5,
                ),
                $prodId,
                $origin
            );
        }
        if ($element && $this->updateExistingReleases) {
            $this->updateReleaseDTO($element, $release, $origin);
        }
        return $element;
    }

    protected function createRelease($releaseInfo, $prodId, $origin)
    {
        return $this->createReleaseDTO(ReleaseImportDTO::fromArray($releaseInfo), (string)$prodId, (string)$origin);
    }

    protected function createReleaseDTO(ReleaseImportDTO $release, string $prodId, string $origin): bool|zxReleaseElement
    {
        $element = false;
        if ($prodElement = $this->getElementByImportId($prodId, $origin, 'prod')) {
            if ($element = $this->structureManager->createElement('zxRelease', 'show', $prodElement->id)) {
                $element->persistStructureLinks();
                $this->saveImportId($element->getId(), $release->id, $origin, 'release');
                $this->updateReleaseDTO($element, $release, $origin, true);
            }
        }
        return $element;
    }

    protected function updateRelease($element, array $releaseInfo, $origin, bool $justCreated = false): void
    {
        $this->updateReleaseDTO($element, ReleaseImportDTO::fromArray($releaseInfo), (string)$origin, $justCreated);
    }

    protected function updateReleaseDTO(zxReleaseElement $element, ReleaseImportDTO $release, string $origin, bool $justCreated = false): void
    {
        $changed = false;

        if (($this->forceUpdateTitles || !$element->title) && !empty($release->title)) {
            $element->title = $release->title;
            $element->structureName = $release->title;
            $changed = true;
        }
        if ((!$element->year || $this->forceUpdateYear) && !empty($release->year)) {
            $element->year = $release->year;
            $changed = true;
        }
        if (!$element->hardwareRequired && !empty($release->hardwareRequired)) {
            $element->hardwareRequired = array_unique($release->hardwareRequired);
            $changed = true;
        }
        if (($this->forceUpdateReleaseType || $justCreated) && (!$element->releaseType || $element->releaseType === 'unknown') && !empty($release->releaseType)) {
            $element->releaseType = $release->releaseType;
            $changed = true;
        }
        if (!$element->language && !empty($release->language)) {
            $element->language = $release->language;
            $changed = true;
        }
        if (!$element->version && !empty($release->version)) {
            $element->version = $release->version;
            $changed = true;
        }

        if (($this->forceUpdateReleaseFiles || $justCreated)) {
            if (!empty($release->filePath)) {
                $destinationFolder = $element->getUploadedFilesPath();
                $info = pathinfo($release->filePath);
                $element->file = $element->getId();
                $element->fileName = $info['filename'] . '.' . $info['extension'];
                $element->parsed = 0;
                $changed = true;
                copy($release->filePath, $destinationFolder . $element->file);
            } elseif (!empty($release->fileUrl)) {
                $info = pathinfo($release->fileUrl);
                $fileName = !empty($release->fileName)
                    ? $release->fileName
                    : ($info['filename'] . '.' . ($info['extension'] ?? ''));
                if ($element->fileName != $fileName || !is_file($element->getFilePath())) {
                    $changed = true;
                    $element->file = $element->getId();
                    $element->fileName = $fileName;
                    $element->parsed = 0;
                    $this->prodsDownloader->moveFileContents($element->getFilePath(), $release->fileUrl);
                }
            }
        }

        if (!empty($release->description) && !$element->description) {
            $element->description = $release->description;
            $changed = true;
        }

        if (($this->forceUpdateReleasePublishers || $this->forceUpdateReleaseAuthors || $justCreated) && !empty($release->labels)) {
            $this->importLabelsInfo(array_map(static fn($l) => $l->toArray(), $release->labels), $origin);
        }

        if (!empty($release->undetermined)) {
            foreach ($release->undetermined as $undeterminedId => $roles) {
                if ($this->getElementIdByImportId($undeterminedId, $origin, 'group')) {
                    $releasePublishers = $release->publishers ?? [];
                    $releasePublishers[] = $undeterminedId;
                } elseif ($this->getElementIdByImportId($undeterminedId, $origin, 'author')) {
                    $releaseAuthors = $release->authors ?? [];
                    $releaseAuthors[$undeterminedId] = $roles;
                }
            }
        }

        if (($this->forceUpdateReleaseAuthors || $justCreated) && !empty($release->authors)) {
            if (!$element->getAuthorsInfo('release')) {
                foreach ($release->authors as $importAuthorId => $roles) {
                    if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                        $this->linkReleaseWithAuthor($authorId, $element->id, $roles);
                    }
                }
            }
        }

        if (($this->forceUpdateReleasePublishers || $justCreated) && !empty($release->publishers)) {
            if (!$element->getPublishersIds()) {
                foreach ($release->publishers as $importPublisherId) {
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

        if (!empty($release->images) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('screenshotsSelector'))) {
            $this->importElementFiles($element, $release->images, 'screenshotsSelector');
        }
        if (!empty($release->inlayImages)) {
            $this->importElementFiles($element, $release->inlayImages, 'inlayFilesSelector');
        }
        if (isset($release->infoFiles)) {
            $this->importElementFiles($element, $release->infoFiles, 'infoFilesSelector');
        }
        if (isset($release->adFiles)) {
            $this->importElementFiles($element, $release->adFiles, 'adFilesSelector');
        }

        if (!empty($release->fileUrl)) {
            $this->prodsDownloader->removeFile($release->fileUrl);
        }
    }

    public function getReleasesByIdList(Builder|null $idList, array|null $sort = [], int|null $start = null, int|null $amount = null)
    {
        return $this->loadReleases($idList, $sort, $start, $amount);
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
