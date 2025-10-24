<?php
declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ElementsManager;
use fileElement;
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
use ZxArt\FileParsing\ZxParsingManager;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Labels\Label;
use ZxArt\Import\Labels\LabelTransformer;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Prods\ProdResolver;
use ZxArt\Parties\Services\PartiesService;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\ZxProdCategories\CategoryIds;
use zxProdElement;
use zxReleaseElement;

class ProdsService extends ElementsManager
{
    use ImportIdOperatorTrait;
    use ReleaseFormatsProvider;
    use ReleaseFileTypesGatherer;

    protected const string TABLE = ProdsRepository::TABLE;

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
        protected AuthorsService       $authorsService,
        protected linksManager         $linksManager,
        protected ProdsDownloader      $prodsDownloader,
        protected privilegesManager    $privilegesManager,
        protected PathsManager         $pathsManager,
        protected AuthorshipRepository $authorshipRepository,
        protected Connection           $db,
        protected LanguagesManager     $languagesManager,
        protected ProdResolver         $prodResolver,
        private LabelTransformer       $labelTransformer,
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

    public function setUpdateExistingProds(bool $updateExistingProds): void
    {
        $this->updateExistingProds = $updateExistingProds;
    }

    public function setForceUpdateYear(bool $forceUpdateYear): void
    {
        $this->forceUpdateYear = $forceUpdateYear;
    }

    public function setForceUpdateYoutube(bool $forceUpdateYoutube): void
    {
        $this->forceUpdateYoutube = $forceUpdateYoutube;
    }

    public function setForceUpdateGroups(bool $forceUpdateGroups): void
    {
        $this->forceUpdateGroups = $forceUpdateGroups;
    }

    public function setForceUpdatePublishers(bool $forceUpdatePublishers): void
    {
        $this->forceUpdatePublishers = $forceUpdatePublishers;
    }

    public function setForceUpdateAuthors(bool $forceUpdateAuthors): void
    {
        $this->forceUpdateAuthors = $forceUpdateAuthors;
    }

    public function setPathsManager(PathsManager $pathsManager): void
    {
        $this->pathsManager = $pathsManager;
    }

    public function setForceUpdateCategories(bool $forceUpdateCategories): void
    {
        $this->forceUpdateCategories = $forceUpdateCategories;
    }

    public function setAddImages(bool $addImages): void
    {
        $this->addImages = $addImages;
    }

    public function setForceUpdateTitles(bool $forceUpdateTitles): void
    {
        $this->forceUpdateTitles = $forceUpdateTitles;
    }

    public function importProdOld(array $prodInfo, string $origin): ?zxProdElement
    {
        return $this->importProd(ProdImportDTO::fromArray($prodInfo), $origin);
    }

    public function importProd(ProdImportDTO $dto, string $origin): ?zxProdElement
    {
        $prodId = $dto->id;
        $element = $this->getElementByImportId($prodId, $origin, 'prod');

        if (!$element && $dto->ids !== null) {
            foreach ($dto->ids as $idOrigin => $id) {
                if ($element = $this->getElementByImportId($id, $idOrigin, 'prod')) {
                    $this->saveImportId($element->id, $prodId, $origin, 'prod');
                    break;
                }
            }
        }

        if (!$element) {
            if ($candidate = $this->getProdByReleaseMd5DTO($dto)) {
                $element = $candidate;
                $this->saveImportId($element->id, $prodId, $origin, 'prod');
            }
        }

        if (!$element) {
            if ($resolved = $this->prodResolver->resolve($dto, $this->matchProdsWithoutYear)) {
                $element = $resolved;
                $this->saveImportId($element->id, $prodId, $origin, 'prod');
            }
        }

        if ($element === null) {
            $element = $this->createProd($dto, $origin);
        } elseif ($this->updateExistingProds) {
            $element = $this->updateProd($element, $dto, $origin);
        }

        if ($element && $dto->releases !== null) {
            foreach ($dto->releases as $release) {
                $this->importRelease($release, $dto->id, $origin);
            }
        }

        return $element;
    }

    private function createProd(ProdImportDTO $dto, string $origin): ?zxProdElement
    {
        $category = null;
        if (!empty($dto->directCategories)) {
            $category = $dto->directCategories[0];
        }
        if (!$category) {
            $category = $this->defaultCategoryId;
        }

        $element = $this->structureManager->createElement('zxProd', 'show', $category, false, 'zxProdCategory');
        if ($element instanceof zxProdElement) {
            $element->dateAdded = time();
            $this->saveImportId($element->getId(), $dto->id, $origin, 'prod');
            $this->updateProd($element, $dto, $origin, true);
            return $element;
        }
        return null;
    }

    /**
     * @param Label[] $labelsList
     */
    private function importLabelsInfo(array $labelsList, string $origin): void
    {
        $labelsList = array_reverse($labelsList);
        foreach ($labelsList as $label) {
            $personLabel = $this->labelTransformer->toPersonLabel($label);
            $groupLabel = $this->labelTransformer->toGroupLabel($label);

            if ($label->isAlias && $label->isPerson) {
                $this->authorsService->importAuthorAlias($personLabel, $origin);
            } elseif ($label->isGroup) {
                $this->groupsService->importGroup($groupLabel, $origin);
            } elseif ($label->isPerson) {
                $this->authorsService->importAuthor($personLabel, $origin);
            } else {
                //we don't know anything about this label. lets search for any group with that name
                $element = $this->groupsService->resolveGroupByLabel($groupLabel, $origin);
                if ($element !== null) {
                    continue;
                }
                //search for author alias with that name
                $element = $this->authorsService->resolveAuthorAliasByLabel($personLabel, $origin);
                if ($element !== null) {
                    continue;
                }
                //search for author with that name
                $element = $this->authorsService->resolveAuthorByLabel($personLabel, $origin);
                if ($element !== null) {
                    continue;
                }

                //just create author by default.
                $this->authorsService->importAuthor($personLabel, $origin);
            }
        }
    }

    private function updateProd(zxProdElement $element, ProdImportDTO $dto, string $origin, bool $justCreated = false): zxProdElement
    {
        $changed = false;
        $dtoTitle = $this->sanitizeTitle($dto->title ?? '');
        if ($dtoTitle !== '' && ($element->title !== $dtoTitle)) {
            if (!$element->title || $this->forceUpdateTitles) {
                $element->title = $dtoTitle;
                $element->structureName = $dtoTitle;
                $changed = true;
            }
        }
        if (!empty($dto->altTitle) && ($element->altTitle !== $dto->altTitle)) {
            $element->altTitle = $dto->altTitle;
            $changed = true;
        }
        if (!empty($dto->legalStatus) && (empty($element->legalStatus) || $justCreated) && $element->legalStatus != $dto->legalStatus) {
            $element->legalStatus = $dto->legalStatus;
            $changed = true;
        }
        if (!empty($dto->year) && (($element->year != $dto->year) && (!$element->year || $this->forceUpdateYear || $justCreated))) {
            $element->year = $dto->year;
            $changed = true;
        }
        if (!empty($dto->compo) && ($element->compo != $dto->compo)) {
            $element->compo = $dto->compo;
            $changed = true;
        }
        if (!empty($dto->description) && !$element->description) {
            $element->description = $dto->description;
            $changed = true;
        }
        if (!empty($dto->party) && (!$element->party || (!empty($dto->party->place) && !$element->partyplace))) {
            $partyTitle = $dto->party->title ?? null;
            $partyYear = $dto->party->year ?? null;
            if ($partyTitle && $partyYear) {
                if ($partyElement = $this->partiesService->getPartyByTitleAndYear($partyTitle, $partyYear)) {
                    if ($element->party !== $partyElement->id) {
                        $element->party = $partyElement->id;
                        $element->renewPartyLink();
                        $changed = true;
                    }
                    if (!empty($dto->party->place)) {
                        if ($element->partyplace !== $dto->party->place) {
                            $element->partyplace = $dto->party->place;
                            $changed = true;
                        }
                    }
                }
            }
        }
        if (!empty($dto->youtubeId) && ($element->youtubeId != $dto->youtubeId) && ($this->forceUpdateYoutube || $justCreated)) {
            $element->youtubeId = $dto->youtubeId;
            $changed = true;
        }
        if (!empty($dto->externalLink) && (($element->externalLink != $dto->externalLink) && ($this->forceUpdateExternalLink || $justCreated))) {
            $element->externalLink = $dto->externalLink;
            $changed = true;
        }
        if (!empty($dto->language) && $element->language != $dto->language) {
            $element->language = $dto->language;
            $changed = true;
        }

        if ($changed) {
            $element->persistElementData();
        }

        if (!empty($dto->labels) && ($this->forceUpdatePublishers || $this->forceUpdateGroups || $this->forceUpdateAuthors || $justCreated)) {
            $this->importLabelsInfo($dto->labels, $origin);
        }

        if (!empty($dto->directCategories)) {
            if ($this->forceUpdateCategories || $justCreated || !$element->getConnectedCategoriesIds()) {
                foreach ($dto->directCategories as $categoryId) {
                    $this->linksManager->linkElements($categoryId, $element->id, 'zxProdCategory');
                }
            }
        }

        if (!empty($dto->undetermined)) {
            foreach ($dto->undetermined as $undeterminedId => $roles) {
                $existingElement = $this->getElementIdByImportId($undeterminedId, $origin, 'group');
                if ($existingElement === null) {
                    $authorId = $this->getElementIdByImportId($undeterminedId, $origin, 'author');
                    if ($authorId !== null) {
                        $this->authorshipRepository->addAuthorship($element->id, $authorId, 'prod', $roles);
                    }
                }
            }
        }

        $authorsInfo = $element->getAuthorsInfo('prod');
        $authorRoles = $dto->authorRoles ?? [];
        if (($this->forceUpdateAuthors || $justCreated || !$authorsInfo) && ($authorRoles !== [])) {
            foreach ($authorRoles as $importAuthorId => $roles) {
                if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                    $this->authorshipRepository->addAuthorship($element->id, $authorId, 'prod', $roles);
                }
            }
        }

        if (!empty($dto->groups) && (!$element->groups || $this->forceUpdateGroups)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdGroups', 'child');
            foreach ($dto->groups as $importGroupId) {
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

        if (!empty($dto->publishers) && (!$element->publishers || $this->forceUpdatePublishers)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdPublishers', 'child');
            foreach ($dto->publishers as $importPublisherId) {
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

        if (!empty($dto->compilationItems)) {
            if (!$element->compilationItems) {
                foreach ($dto->compilationItems as $importItemId) {
                    if ($prodId = $this->getElementIdByImportId($importItemId, $origin, 'prod')) {
                        $this->linksManager->linkElements($element->id, $prodId, 'compilation');
                    } elseif ($releaseId = $this->getElementIdByImportId($importItemId, $origin, 'release')) {
                        $this->linksManager->linkElements($element->id, $releaseId, 'compilation');
                    }
                }
            }
        }

        if (!empty($dto->seriesProds)) {
            if (!$element->seriesProds) {
                foreach ($dto->seriesProds as $importItemId) {
                    if ($prodId = $this->getElementIdByImportId($importItemId, $origin, 'prod')) {
                        $this->linksManager->linkElements($element->id, $prodId, 'series');
                    }
                }
            }
        }

        if (!empty($dto->articles)) {
            if (!$element->articles) {
                foreach ($dto->articles as $article) {
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

        if (!empty($dto->categories) && (!$element->getConnectedCategoriesIds() || $this->forceUpdateCategories || $justCreated)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->id, 'zxProdCategory', 'child');
            foreach ($dto->categories as $importCategoryId) {
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

        if (!empty($dto->images) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('connectedFile'))) {
            $this->importElementFiles($element, $dto->images);
            if ($this->resizeImages) {
                $element->resizeImages();
            }
        }

        if (!empty($dto->maps) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('mapFilesSelector'))) {
            $propertyName = 'mapFilesSelector';
            $existingFiles = $element->getFilesList($propertyName);
            foreach ($dto->maps as $map) {
                try {
                    $this->importElementFile($element, $map->url, $existingFiles, $map->author, $propertyName);
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }

        if (!empty($dto->inlayImages) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('inlayFilesSelector'))) {
            $this->importElementFiles($element, $dto->inlayImages, 'inlayFilesSelector');
        }

        if (!empty($dto->rzx)) {
            $propertyName = 'rzx';
            $existingFiles = $element->getFilesList($propertyName);
            foreach ($dto->rzx as $rzx) {
                try {
                    $this->importElementFile($element, $rzx->url, $existingFiles, $rzx->author, $propertyName);
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }

        if (!empty($dto->importIds)) {
            foreach ($dto->importIds as $importOrigin => $id) {
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
    private function importElementFile(zxReleaseElement|zxProdElement $element, string $fileUrl, array $existingFiles, string|null $fileAuthor = null, string $propertyName = 'connectedFile'): void
    {
        $this->structureManager->setNewElementLinkType($element->getConnectedFileType($propertyName));
        $uploadsPath = $this->pathsManager->getPath('uploads');

        $originalFileName = basename($fileUrl);
        $fileExists = false;
        foreach ($existingFiles as $existingFile) {
            if ($originalFileName === urldecode($existingFile->fileName)) {
                $path = $existingFile->getFilePath();
                if (is_file($path)) {
                    $size = filesize($path);
                    if ($size > 0 && is_file($path)) {
                        $fileExists = true;
                        break;
                    }
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
            /**
             * @var fileElement $fileElement
             */
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
                $fileElement->author = $fileAuthor ?? '';
                rename($filePath, $destinationFolder . $fileElement->file);
                $fileElement->persistElementData();

                $element->appendFileToList($fileElement, $propertyName);
            }
        }

        $this->structureManager->setNewElementLinkType();

    }

    protected function importElementFiles(zxReleaseElement|zxProdElement $element, array $images, string $propertyName = 'connectedFile'): void
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
                    if ($releaseElement = $this->getReleaseByMd5($release, $prod->id)) {
                        return $releaseElement->getProd();
                    }
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }
        return null;
    }

    private function getReleaseByMd5(ReleaseImportDTO $dto, string $prodId): ?zxReleaseElement
    {
        if (!$dto->fileUrl) {
            return null;
        }
        if (empty($dto->md5)) {
            $path = $this->prodsDownloader->getDownloadedPath($dto->fileUrl);
            if (!$path) {
                sleep(10);
                $path = $this->prodsDownloader->getDownloadedPath($dto->fileUrl);
            }
            if (!$path) {
                throw new ReleaseDownloadException('Unable to download release ' . $prodId . ' ' . $dto->id . ' ' . $dto->title . ' ' . $dto->fileUrl);
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

    private function importRelease(ReleaseImportDTO $dto, string $prodId, string $origin): bool|zxReleaseElement
    {
        $releaseId = $dto->id;
        $sanitizedTitle = $this->sanitizeTitle($dto->title);

        $element = $this->getElementByImportId($releaseId, $origin, 'release');
        if (!$element) {
            try {
                if ($candidate = $this->getReleaseByMd5($dto, $prodId)) {
                    $element = $candidate;
                    $this->saveImportId($element->id, $releaseId, $origin, 'release');
                }
            } catch (ReleaseDownloadException $e) {
                $this->logError($e->getMessage());
            }
        }
        if (!$element) {
            $element = $this->createRelease(
                new ReleaseImportDTO(
                    id: $dto->id,
                    title: $sanitizedTitle,
                    year: $dto->year,
                    language: $dto->language,
                    version: $dto->version,
                    releaseType: $dto->releaseType,
                    filePath: $dto->filePath,
                    fileUrl: $dto->fileUrl,
                    fileName: $dto->fileName,
                    description: $dto->description,
                    hardwareRequired: $dto->hardwareRequired,
                    labels: $dto->labels,
                    authors: $dto->authors,
                    publishers: $dto->publishers,
                    undetermined: $dto->undetermined,
                    images: $dto->images,
                    inlayImages: $dto->inlayImages,
                    infoFiles: $dto->infoFiles,
                    adFiles: $dto->adFiles,
                    md5: $dto->md5,
                ),
                $prodId,
                $origin
            );
        }
        if ($element && $this->updateExistingReleases) {
            $this->updateRelease($element, $dto, $origin);
        }
        return $element;
    }

    protected function createRelease(ReleaseImportDTO $dto, string $prodId, string $origin): bool|zxReleaseElement
    {
        $element = false;
        if ($prodElement = $this->getElementByImportId($prodId, $origin, 'prod')) {
            /**
             * @var zxReleaseElement $element
             */
            if ($element = $this->structureManager->createElement('zxRelease', 'show', $prodElement->id)) {
                $element->persistStructureLinks();
                $this->saveImportId($element->getId(), $dto->id, $origin, 'release');
                $this->updateRelease($element, $dto, $origin, true);
            }
        }
        return $element;
    }

    private function updateRelease(zxReleaseElement $element, ReleaseImportDTO $dto, string $origin, bool $justCreated = false): void
    {
        $changed = false;

        if (($this->forceUpdateTitles || !$element->title) && !empty($dto->title)) {
            $element->title = $dto->title;
            $element->structureName = $dto->title;
            $changed = true;
        }
        if ((!$element->year || $this->forceUpdateYear) && !empty($dto->year)) {
            $element->year = $dto->year;
            $changed = true;
        }
        if (!$element->hardwareRequired && !empty($dto->hardwareRequired)) {
            $element->hardwareRequired = array_unique($dto->hardwareRequired);
            $changed = true;
        }
        if (($this->forceUpdateReleaseType || $justCreated) && (!$element->releaseType || $element->releaseType === 'unknown') && !empty($dto->releaseType)) {
            $element->releaseType = $dto->releaseType;
            $changed = true;
        }
        if (!$element->language && !empty($dto->language)) {
            $element->language = $dto->language;
            $changed = true;
        }
        if (!$element->version && !empty($dto->version)) {
            $element->version = $dto->version;
            $changed = true;
        }

        if (($this->forceUpdateReleaseFiles || $justCreated)) {
            if (!empty($dto->filePath)) {
                $destinationFolder = $element->getUploadedFilesPath();
                $info = pathinfo($dto->filePath);
                $element->file = $element->getId();
                $element->fileName = $info['filename'] . '.' . $info['extension'];
                $element->parsed = 0;
                $changed = true;
                copy($dto->filePath, $destinationFolder . $element->file);
            } elseif (!empty($dto->fileUrl)) {
                $info = pathinfo($dto->fileUrl);
                $fileName = !empty($dto->fileName)
                    ? $dto->fileName
                    : ($info['filename'] . '.' . ($info['extension'] ?? ''));
                if ($element->fileName != $fileName || !is_file($element->getFilePath())) {
                    $changed = true;
                    $element->file = $element->getId();
                    $element->fileName = $fileName;
                    $element->parsed = 0;
                    $this->prodsDownloader->moveFileContents($element->getFilePath(), $dto->fileUrl);
                }
            }
        }

        if (!empty($dto->description) && !$element->description) {
            $element->description = $dto->description;
            $changed = true;
        }

        if (($this->forceUpdateReleasePublishers || $this->forceUpdateReleaseAuthors || $justCreated) && !empty($dto->labels)) {
            $this->importLabelsInfo($dto->labels, $origin);
        }

        if (!empty($dto->undetermined)) {
            foreach ($dto->undetermined as $undeterminedId => $roles) {
                if ($this->getElementIdByImportId($undeterminedId, $origin, 'group')) {
                    $releasePublishers = $dto->publishers ?? [];
                    $releasePublishers[] = $undeterminedId;
                } elseif ($this->getElementIdByImportId($undeterminedId, $origin, 'author')) {
                    $releaseAuthors = $dto->authors ?? [];
                    $releaseAuthors[$undeterminedId] = $roles;
                }
            }
        }

        if (($this->forceUpdateReleaseAuthors || $justCreated) && !empty($dto->authors)) {
            if (!$element->getAuthorsInfo('release')) {
                foreach ($dto->authors as $importAuthorId => $roles) {
                    if ($authorId = $this->getElementIdByImportId($importAuthorId, $origin, 'author')) {
                        $this->linkReleaseWithAuthor($authorId, $element->id, $roles);
                    }
                }
            }
        }

        if (($this->forceUpdateReleasePublishers || $justCreated) && !empty($dto->publishers)) {
            if (!$element->getPublishersIds()) {
                foreach ($dto->publishers as $importPublisherId) {
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

        if (!empty($dto->images) && ($this->forceUpdateImages || $justCreated || !$element->getFilesList('screenshotsSelector'))) {
            $this->importElementFiles($element, $dto->images, 'screenshotsSelector');
        }
        if (!empty($dto->inlayImages)) {
            $this->importElementFiles($element, $dto->inlayImages, 'inlayFilesSelector');
        }
        if (isset($dto->infoFiles)) {
            $this->importElementFiles($element, $dto->infoFiles, 'infoFilesSelector');
        }
        if (isset($dto->adFiles)) {
            $this->importElementFiles($element, $dto->adFiles, 'adFilesSelector');
        }

        if (!empty($dto->fileUrl)) {
            $this->prodsDownloader->removeFile($dto->fileUrl);
        }
    }

    public function getReleasesByIdList(Builder|null $idList, array|null $sort = [], int|null $start = null, int|null $amount = null): ?array
    {
        return $idList !== null ? $this->loadReleases($idList, $sort, $start, $amount) : null;
    }

    public function makeReleasesQuery(): Builder
    {
        return $this->db->table('module_zxrelease');
    }

    protected function loadReleases(Builder $query, array|null $sort = [], int|null $start = null, int|null $amount = null): array
    {
        if (is_array($sort)) {
            foreach ($sort as $property => $order) {
                if (isset($this->releaseColumnRelations[$property])) {
                    $srcTableName = $this->db->getTablePrefix() . $query->from;
                    foreach ($this->releaseColumnRelations[$property] as $criteria => $orderDirection) {
                        if ($criteria === 'dateCreated') {
                            $query->leftJoin('structure_elements', 'structure_elements.id', '=', $query->from . '.id');
                            $query->orderBy("structure_elements.dateCreated", $orderDirection);
                        } else {
                            $orderColumn = $criteria === 'title' ? "LOWER($srcTableName.$criteria)" : "$srcTableName.$criteria";

                            if ($orderDirection === true) {
                                $query->orderByRaw("$orderColumn $order");
                            } else {
                                if ($orderDirection === false) {
                                    if ($order === 'desc') {
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

        return $title;
    }

    public function joinDeleteZxProd(int $mainZxProdId, int $joinedZxProdId, bool $releasesOnly = false): bool
    {
        if ($joinedZxProdId === $mainZxProdId) {
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
                        if (!$mainZxProd->legalStatus || $mainZxProd->legalStatus === 'unknown') {
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
