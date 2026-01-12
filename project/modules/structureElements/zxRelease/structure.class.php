<?php

use Illuminate\Database\Connection;
use ZxArt\FileParsing\ZxParsingItem;
use ZxArt\FileParsing\ZxParsingManager;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Prods\LegalStatus;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueType;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ArchiveFileResolverService;
use ZxArt\Releases\Services\EmulatorResolverService;
use ZxArt\Releases\Services\ReleaseFileTypesGatherer;
use ZxFiles\BasicFile;

/**
 * @property string $title
 * @property string $file
 * @property string $fileName
 * @property int $downloads
 * @property int $plays
 * @property int $year
 * @property string $releaseType
 * @property string|null $description
 * @property string $version
 * @property array $hardwareRequired
 * @property array $language
 * @property array $releaseFormat
 * @property zxProdElement[] $compilations
 * @property authorElement[]|authorAliasElement[]|groupElement[]|groupAliasElement[] $publishers
 * @property float $votes
 * @property int $denyVoting
 * @property int $denyComments
 * @property int $dateAdded
 * @property int $userId
 * @property int $parsed
 */
class zxReleaseElement extends ZxArtItem implements
    StructureElementUploadedFilesPathInterface,
    CommentsHolderInterface,
    JsonDataProvider,
    ZxSoftInterface,
    Recalculable,
    BreadcrumbsInfoProvider
{
    use AuthorshipProviderTrait;
    use AuthorshipPersister;
    use ImportedItemTrait;
    use HardwareProvider;
    use LanguageCodesProviderTrait;
    use LinksPersistingTrait;
    use PublisherGroupProviderTrait;
    use MaterialsProviderTrait;
    use FilesElementTrait;
    use GalleryInfoProviderTrait;
    use JsonDataProviderElement;
    use ZxSoft;

    public $dataResourceName = 'module_zxrelease';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $viewName = 'short';

    protected $votesType = 'zxRelease';
    protected $partyLinkType = 'partyProd';
    protected $hardwareInfo;
    protected $currentReleaseFileInfo;
    protected $imagesUrls;
    protected zxProdElement|null $prodElement = null;
    private const array MbReleaseTypeRunnable = ['tar'];
    private const array MbHardwareRunnable = ["elementzxmb"];

    private const array UspReleaseTypeRunnable = ['trd', 'tap', 'z80', 'sna', 'tzx', 'scl'];
    private const array Zx81ReleaseTypeRunnable = ['tzx', 'p', 'o'];
    private const array RunnableTypes = [...self::UspReleaseTypeRunnable, ...self::Zx81ReleaseTypeRunnable, ...self::TsconfReleaseTypeRunnable];
    private const array Zx80HardwareRunnable = ["zx80"];
    private const array Zx81HardwareRunnable = ["zx8116", "zx811", "zx812", "zx8132", "zx8164"];

    private const array TsconfReleaseTypeRunnable = ['spg', 'img', 'trd', 'scl'];
    private const array TsconfHardwareRunnable = ["tsconf"];

    public function __construct($rootMarkerPublic)
    {
        parent::__construct($rootMarkerPublic);
    }

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';

        $moduleStructure['file'] = 'file';
        $moduleStructure['fileName'] = 'fileName';

        $moduleStructure['version'] = 'text';
        $moduleStructure['downloads'] = 'text';
        $moduleStructure['plays'] = 'text';
        $moduleStructure['year'] = 'naturalNumber';
        $moduleStructure['publishers'] = [
            'ConnectedElements',
            [
                'linkType' => 'zxReleasePublishers',
                'role' => 'child',
            ],
        ];
        $moduleStructure['zxProd'] = [
            'ConnectedElements',
            [
                'linkType' => 'structure',
                'role' => 'child',
            ],
        ];
        $moduleStructure['description'] = 'html';
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';

        $moduleStructure['dateAdded'] = 'date';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['releaseType'] = 'text';
        $moduleStructure['releaseFormat'] = [
            'DBValueSet',
            [
                'tableName' => $this->dataResourceName . '_format',
            ],
        ];
        $moduleStructure['hardwareRequired'] = [
            'DBValueSet',
            [
                'tableName' => $this->dataResourceName . '_hw_required',
            ],
        ];
        $moduleStructure['language'] = [
            'DBValueSet',
            [
                'tableName' => 'zxitem_language',
            ],
        ];
        $moduleStructure['compilations'] = [
            'ConnectedElements',
            [
                'linkType' => 'compilation',
                'role' => 'child',
            ],
        ];

        $moduleStructure['addAuthor'] = 'text';
        $moduleStructure['addAuthorRole'] = 'array';
        $moduleStructure['parsed'] = 'checkbox';
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'screenshotsSelector', 'inlayFilesSelector', 'infoFilesSelector', 'adFilesSelector'}
     */
    public function getFileSelectorPropertyNames(): array
    {
        return ['screenshotsSelector', 'inlayFilesSelector', 'infoFilesSelector', 'adFilesSelector'];
    }

    public function getUploadedFilesPath($propertyName = 'connectedFile')
    {
        return $this->getService('PathsManager')->getPath('releases');
    }

    /**
     * @return false
     */
    public function getFileUploadSuccessUrl(): bool
    {
        return false;
    }

    /**
     * @return true
     */
    public function isPrivilegesSettingRequired(): bool
    {
        return true;
    }

    /**
     * @return string[]
     *
     * @psalm-return list{0: string, 1?: string,...}
     */
    protected function getDeletionLinkTypes(): array
    {
        $result = ['structure'];
        foreach ($this->getFileSelectorPropertyNames() as $propertyName) {
            $result[] = $this->getConnectedFileType($propertyName);
        }
        return $result;
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
    }

    /**
     * @return false|string
     */
    public function getFileExtension($extensionType)
    {
        $extension = false;
        if (file_exists($this->getFilePath())) {
            if ($info = pathinfo($this->fileName)) {
                if (isset($info['extension'])) {
                    $extension = "." . strtolower($info['extension']);
                }
            }
        }
        return $extension;
    }

    /**
     * @return bool
     */
    protected function fileExists($extensionType)
    {
        if (file_exists($this->getFilePath())) {
            return true;
        }
        return false;
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'view'}
     */
    public function getChartDataEventTypes($type = null)
    {
        return ['view'];
    }

    /**
     * @psalm-return false|non-empty-list<mixed>
     */
    public function getReleaseStructure(): array|false
    {
        if ($structure = $this->getReleaseFlatStructure()) {
            $groups = [];
            foreach ($structure as $key => $item) {
                $groups[$item['parentId']][] = &$structure[$key];
            }
            foreach ($structure as $key => $item) {
                if (isset($groups[$item['id']])) {
                    $structure[$key]['items'] = $groups[$item['id']];
                }
            }
            return $groups['0'];
        }

        return false;
    }

    public function getReleaseFlatStructure()
    {
        if ($this->getFilePath()) {
            return $this->getService(ZxParsingManager::class)->getStructureRecordsById($this->getId());
        }
        return false;
    }

    public function getFilePath(): string
    {
        return $this->getUploadedFilesPath() . $this->file;
    }

    public function getFileUrl(bool $play = false): string
    {
        $controller = $this->getService('controller');
        return $controller->baseURL . 'release/id:' . $this->getId() . '/' . $this->getFileName();
    }

    public function getPlayUrl($serveZip = true): ?string
    {
        $controller = $this->getService('controller');
        if ($serveZip) {
            return $controller->baseURL . 'release/play:1/id:' . $this->getId() . '/' . $this->getFileName();
        }

        $playableFiles = $this->getPlayableFiles();
        $item = $playableFiles[0] ?? null;
        if ($item === null) {
            return null;
        }

        $fileName = $item['fileName'];
        return "{$controller->baseURL}zxfile/id:{$this->getId()}/fileId:{$item['id']}/play:1/{$fileName}";
    }

    public function getPlayableFiles(): array
    {
        $structure = $this->getReleaseFlatStructure();
        $playableFiles = [];
        $types = $this->getRunnableTypes();

        foreach ($structure as $item) {
            $extension = strtolower(pathinfo($item['fileName'], PATHINFO_EXTENSION));
            if (in_array($extension, $types, true)) {
                $playableFiles[] = $item;
            }
        }

        return $playableFiles;
    }

    public function getArchiveFilesForHardware(): array
    {
        $structure = $this->getReleaseFlatStructure() ?: [];
        return $this->getService(ArchiveFileResolverService::class)->filterArchiveFiles($structure, $this->hardwareRequired);
    }


    private function getRunnableTypes(): array
    {
        $emulator = $this->resolveEmulatorType();
        return $this->getService(EmulatorResolverService::class)->getRunnableTypesForEmulator($emulator);
    }

    private function resolveEmulatorType(): ?string
    {
        return $this->getService(EmulatorResolverService::class)->resolveEmulator($this->hardwareRequired, $this->releaseFormat);
    }

    /**
     * @return string
     */
    #[Override]
    public function getFileName(
        $extensionType = 'original',
        $escapeSpaces = true,
        $urlEncode = true,
        $addAuthor = true,
        $addYear = true,
        $addParty = true,
        $addPartyPlace = false,
        $addId = false,
    )
    {
        return $this->fileName;
    }

    public function getProd(): zxProdElement|null
    {
        if ($this->prodElement !== null) {
            return $this->prodElement;
        }
        $parent = $this->getFirstParentElement();
        if ($parent !== null && $parent->structureType === 'zxProd') {
            /**
             * @var zxProdElement $parent
             */
            $this->prodElement = $parent;
            return $this->prodElement;
        }

        $parent = $this->getRequestedParentElement();
        if ($parent !== null && $parent->structureType === 'zxProd') {
            /**
             * @var zxProdElement $parent
             */
            $this->prodElement = $parent;
            return $this->prodElement;
        }

        return null;
    }

    public function getLegalStatus()
    {
        return $this->getProd()?->getLegalStatus() ?? LegalStatus::unknown->name;
    }

    public function getReleaseTypes(): array
    {
        return ReleaseTypes::getAllValues();
    }

    public function getCurrentReleaseContentFormatted()
    {
        if ($file = $this->getCurrentReleaseFileInfo()) {
            return $this->getFormattedFileContent($file);
        }
        return false;
    }

    public function getFormattedFileContent(array $fileRecord): ?string
    {
        $zxParsingManager = $this->getService(ZxParsingManager::class);
        $controller = controller::getInstance();

        $extractedFile = $zxParsingManager->extractFile($this->getFilePath(), $fileRecord['id']);
        if ($extractedFile === null) {
            return null;
        }
        if ($content = $extractedFile->getContent()) {
            switch ($fileRecord['internalType']) {
                case 'plain_text':
                    return htmlspecialchars(mb_convert_encoding($content, 'UTF-8', $fileRecord['encoding']));
                case 'source_code':
                    return htmlspecialchars($content);
                case 'pc_image':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxfile/id:" . $this->getId() . "/fileId:" . $fileId . "/" . $extractedFile->getItemName() . "' />";
                    }
                    break;
                case 'zx_basic':
                    $basic = new BasicFile();
                    $basic->setBinary($content);
                    return htmlspecialchars($basic->getAsText());
                case 'zx_image_standard':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->getId() . "/fileId:" . $fileId . "/type:standard/' />";
                    }
                    break;
                case 'zx_image_monochrome':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->getId() . "/fileId:" . $fileId . "/type:monochrome/' />";
                    }
                    break;
                case 'zx_image_tricolor':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->getId() . "/fileId:" . $fileId . "/type:tricolor/' />";
                    }
                    break;
                case 'zx_image_gigascreen':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->getId() . "/fileId:" . $fileId . "/type:gigascreen/' />";
                    }
                    break;
                default:
                    $hex = new HexViewer();
                    return htmlspecialchars($hex->getFormatted($content));
            }
        }

        return null;
    }

    /**
     * @psalm-return EngineFileRegistryRow|null
     */
    public function getCurrentReleaseFileInfo(): ?array
    {
        if ($this->currentReleaseFileInfo === null) {
            if ($fileId = $this->getFileId()) {
                /**
                 * @var ZxParsingManager $zxParsingManager
                 */
                $zxParsingManager = $this->getService(ZxParsingManager::class);
                if ($fileInfo = $zxParsingManager->getFileRecord($fileId)) {
                    $this->currentReleaseFileInfo = $fileInfo;
                }
            }
        }
        return $this->currentReleaseFileInfo;
    }

    public function getSupportedLanguageCodes()
    {
        if ($this->language) {
            return $this->language;
        } else {
            if ($zxProd = $this->getProd()) {
                return $zxProd->getSupportedLanguageCodes();
            }
        }
        return [];
    }

    #[Override]
    public function getYear(): int|null
    {
        if ($this->year > 0) {
            return $this->year;
        }
        if ($this->releaseType === ReleaseTypes::original->value) {
            $zxProd = $this->getProd();
            if ($zxProd !== null && $zxProd->year > 0) {
                return $zxProd->year;
            }
        }
        return null;
    }

    /**
     * @return fileElement[]
     *
     * @psalm-return array<fileElement>
     */
    public function getImagesList()
    {
        return array_merge($this->getFilesList('screenshotsSelector'), $this->getFilesList('inlayFilesSelector'), $this->getFilesList('adFilesSelector'));
    }

    public function isPlayable(): bool
    {
        return $this->resolveEmulatorType() !== null;
    }

    public function isDownloadable(): bool
    {
        $user = $this->getService('user');
        $privileges = $this->getService('privilegesManager')->getElementPrivileges($this->getId());

        return !in_array($this->getLegalStatus(), [legalStatus::forbidden->name, legalStatus::forbiddenzxart->name, legalStatus::insales->name], true) ||
            $this->releaseType === ReleaseTypes::demoversion->value ||
            !empty($privileges['zxRelease']['downloadDenied']) ||
            (
                ($this->getLegalStatus() !== LegalStatus::insales->name) &&
                $user->isAuthorized() &&
                ($this->getProd()?->year !== 0) &&
                ($this->getProd()?->year < (date('Y') - 20))
            );
    }

    public function getLinksInfo()
    {
        if ($this->linksInfo === null) {
            $prod = $this->getProd();
            $prodStatus = $prod?->getLegalStatus() ?? LegalStatus::unknown->name;
            $this->linksInfo = [];
            $translationsManager = $this->getService('translationsManager');
            /**
             * @var Connection $db
             */
            $db = $this->getService('db');
            $query = $db->table('import_origin')
                ->select('importId', 'importOrigin')
                ->where('elementId', '=', $this->getId())
                ->whereIn('importOrigin', ['vt', 'pouet']);
            if ($rows = $query->get()) {
                foreach ($rows as $row) {
                    if ($row['importOrigin'] === 'pouet') {
                        $this->linksInfo[] = [
                            'type' => 'pouet',
                            'image' => 'icon_pouet.png',
                            'name' => $translationsManager->getTranslationByName('links.link_pouet'),
                            'url' => 'https://www.pouet.net/prod.php?which=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'vt' && $prodStatus !== LegalStatus::insales->name) {
                        $this->linksInfo[] = [
                            'type' => 'vt',
                            'image' => 'icon_vt.png',
                            'name' => $translationsManager->getTranslationByName('links.link_vt'),
                            'url' => 'https://vtrd.in/release.php?r=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    }
                }
            }
        }

        return $this->linksInfo;
    }

    #[Override]
    public function getSearchTitle(): string
    {
        $searchTitle = $this->title;
        $additions = '';
        $strings = [];
        if ($this->year) {
            $strings[] .= $this->year;
        }
        if ($authors = $this->getAuthorsInfo('release', ['release'])) {
            foreach ($authors as $author) {
                $strings[] = $author['authorElement']->title;
            }
        } elseif ($publishers = $this->getPublishersList()) {
            foreach ($publishers as $publisher) {
                $strings[] .= $publisher->title;
            }
        }
        if ($strings) {
            $additions .= implode(', ', $strings);
        }
        if ($additions) {
            $searchTitle .= ' (' . $additions . ')';
        }

        return $searchTitle;
    }

    public function getImageUrl(int $number, $preset = 'prodImage')
    {
        if ($image = $this->getImage($number)) {
            return $image->getImageUrl($preset);
        }

        if ($zxProd = $this->getProd()) {
            return $zxProd->getImageUrl($number, $preset);
        }

        if ($number === 0) {
            $controller = $this->getService('controller');
            return $controller->baseURL . 'images/zxprod_default.png';
        }
        return false;
    }

    public function getImage($number = 0): fileElement|false
    {
        if ($images = $this->getFilesList('screenshotsSelector')) {
            if (isset($images[$number])) {
                return $images[$number];
            }
        }
        return false;
    }

    public function getLdJsonScriptData()
    {
        $data = [
            "@context" => "http://schema.org/",
            "@type" => ["SoftwareApplication"],
            "name" => $this->title,
            "url" => $this->URL,
        ];
        $computersList = array_intersect($this->hardwareRequired, $this->getHardwareList()[HardwareGroup::COMPUTERS->value]);
        $dosList = array_intersect($this->hardwareRequired, $this->getHardwareList()[HardwareGroup::DOS->value]);
        $translationsManager = $this->getService('translationsManager');

        $computersStrings = [];
        foreach ($computersList as $computer) {
            $computersStrings[] = $translationsManager->getTranslationByName('hardware.item_' . $computer);
        }

        $dosStrings = [];
        foreach ($dosList as $dos) {
            $dosStrings[] = $translationsManager->getTranslationByName('hardware.item_' . $dos);
        }

        $prod = $this->getProd();

        $data['availableOnDevice'] = $computersStrings;
        $data['operatingSystem'] = $dosStrings;
        $data['applicationSubCategory'] = $prod?->getCategoriesString();
        $data['applicationCategory'] = $prod?->getRootCategoriesString();

        $data["description"] = $this->getTextContent();
        $data["commentCount"] = $this->commentsAmount;
        $data["author"] = [
            "@type" => 'Person',
            "name" => $this->getAuthorNamesString(),
        ];
        if ($this->votesAmount) {
            $data["aggregateRating"] = [
                "@type" => 'AggregateRating',
                "ratingValue" => $this->votes,
                "ratingCount" => $this->votesAmount,
            ];
        }
        if ($imageUrl = $this->getImageUrl(1)) {
            $data['screenshot'] = $imageUrl;
            $data['image'] = $imageUrl;
            $data['thumbnailUrl'] = $imageUrl;
        }
        if ($this->year) {
            $data['datePublished'] = $this->year;
        }
        if ($this->isDownloadable()) {
            $data['downloadUrl'] = $this->getFileUrl();
        }

        if ($tags = $this->generateTagsText()) {
            $data['keywords'] = $tags;
        }
        return $data;
    }


    /**
     * @psalm-return array<never, never>
     */
    public function getBestPictures(): array
    {
        return [];
    }

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getReleaseBy(): array
    {
        $list = [];

        foreach ($this->publishers as $publisher) {
            $list[] = $publisher;
        }
        if ($authors = $this->getAuthorsInfo('release', ['release'])) {
            foreach ($authors as $author) {
                $list[] = $author['authorElement'];
            }
        }
        return $list;
    }

    /**
     * @return string
     */
    #[Override]
    public function getMetaTitle()
    {
        $translationsManager = $this->getService('translationsManager');
        $title = $this->title;

        $fileInfo = $this->getCurrentReleaseFileInfo();
        if ($fileInfo !== null) {
            $title .= ' (' . $fileInfo['fileName'] . ')';
            return $title;
        }

        $title .= ' - ZX Spectrum release';

        if ($info = $this->getReleaseBy()) {
            $title .= ' by ';
            $title .= implode(', ', array_map(static function ($item) {
                return $item->getTitle();
            }, $info));
        }
        if ($this->releaseType) {
            $title .= ', ' . $translationsManager->getTranslationByName('zxRelease.type_' . $this->releaseType);
        }
        if ($this->year) {
            $title .= ' ' . $this->year;
        }

        return $title;

    }

    //used in API

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getImagesUrls($preset = 'prodImage'): array
    {
        $urls = [];
        foreach ($this->getFilesList('screenshotsSelector') as $fileElement) {
            $urls[] = $fileElement->getImageUrl($preset);
        }
        return $urls;
    }

    /**
     * @return (mixed|string)[][]
     *
     * @psalm-return list{0?: array{id: mixed, title: string, url: mixed},...}
     */
    public function getPublishersInfo(): array
    {
        $publishersInfo = [];
        foreach ($this->publishers as $publisher) {
            $publishersInfo[] = [
                'id' => $publisher->id,
                'title' => html_entity_decode($publisher->title, ENT_QUOTES),
                'url' => $publisher->getUrl(),
            ];
        }
        return $publishersInfo;
    }

    public function getHardwareInfo()
    {
        if (!isset($this->hardwareInfo)) {
            /**
             * @var languagesManager $languagesManager
             */
            $languagesManager = $this->getService('languagesManager');
            $key = 'hw' . $languagesManager->getCurrentLanguageId();
            if (($this->hardwareInfo = $this->getCacheKey($key)) === null) {
                $this->hardwareInfo = [];
                /**
                 * @var translationsManager $translationsManager
                 */
                $translationsManager = $this->getService('translationsManager');
                foreach ($this->hardwareRequired as $item) {
                    $this->hardwareInfo[] = [
                        'id' => $item,
                        'title' => html_entity_decode($translationsManager->getTranslationByName('hardware_short.item_' . $item), ENT_QUOTES),
                    ];
                }
                $this->setCacheKey($key, $this->hardwareInfo, 24 * 60 * 60);
            }
        }
        return $this->hardwareInfo;
    }

    /**
     * @return void
     */
    public function persistElementData()
    {
        if ($this->title === '') {
            $prod = $this->getProd();
            $this->title = $prod?->title ?? '';
            $this->structureName = $this->title;
        }
        $this->optimizeAliases('publishers');

        if (!$this->releaseType) {
            $this->releaseType = ReleaseTypes::unknown->value;
        }
        parent::persistElementData();

        if ($this->newlyCreated) {
            $queueService = $this->getService(QueueService::class);
            $queueService->checkElementInQueue($this->getPersistedId(), [QueueType::SOCIAL_POST]);
        }
    }

    public function updateFileStructure(): void
    {
        $zxParsingManager = $this->getService(ZxParsingManager::class);
        $releaseFileTypesGatherer = $this->getService(ReleaseFileTypesGatherer::class);

        $filePath = $this->getFilePath();
        $id = $this->getPersistedId();

        if (empty($filePath) || !is_file($filePath)) {
            $zxParsingManager->deleteFileStructure($id);
            $this->parsed = 1;
            $this->releaseFormat = [];
            $this->persistElementData();
            return;
        }

        $actualMd5 = md5_file($filePath);
        $topRecord = $zxParsingManager->getTopFileRecord($id);
        $baseMd5 = $topRecord['md5'] ?? null;

        if ($baseMd5 === null || $baseMd5 !== $actualMd5) {
            $zxParsingManager->updateFileStructure(
                $id,
                urldecode($filePath),
                $this->fileName
            );
        }
        $structure = $zxParsingManager->getFileStructure($id);
        if ($structure) {
            $files = $releaseFileTypesGatherer->gatherReleaseFiles($structure);
            if (!empty($files)) {
                $types = array_map(static fn(ZxParsingItem $item) => $item->getItemExtension(), $files);
                $this->releaseFormat = array_values(array_unique($types));
            }
        }

        $this->parsed = 1;
        $this->persistElementData();
    }

    public function getCompilationJsonData(): string|false
    {
        $data = [
            'compilations' => [],
        ];
        foreach ($this->compilations as $prod) {
            $data['compilations'][] = $prod->getElementData('list');
        }
        return json_encode($data);
    }

    public function recalculate(): void
    {
        $this->persistElementData();
    }

    public function incrementPlays(): void
    {
        $db = $this->getService('db');
        $db->table('module_zxrelease')->where('id', '=', $this->getId())->limit(1)->increment('plays');
        $structureManager = $this->getService('structureManager');
        $structureManager->clearElementCache($this->getId());
    }

    public function incrementDownloads(): void
    {
        $db = $this->getService('db');
        $db->table('module_zxrelease')->where('id', '=', $this->getId())->limit(1)->increment('downloads');
        $structureManager = $this->getService('structureManager');
        $structureManager->clearElementCache($this->getId());
    }

    public function getEmulatorType(): ?string
    {
        return $this->resolveEmulatorType();
    }

    #[Override]
    public function getCanonicalUrl()
    {
        $url = parent::getCanonicalUrl();
        if ($this->actionName === 'viewFile') {
            $fileId = $this->getFileId();
            if ($fileId === null) {
                return $url;
            }
            $url .= "action:viewFile/id:{$this->getId()}/fileId:{$fileId}/";
        }
        return $url;
    }

    private function getFileId(): ?int
    {
        $controller = controller::getInstance();
        $fileId = $controller->getParameter('fileId');
        return $fileId === false ? null : (int)$fileId;
    }

    public function getCatalogueUrl($parameters): string
    {
        $prodElement = $this->getProd();
        if (!$prodElement) {
            return '';
        }
        return $prodElement->getCatalogueUrl($parameters);
    }

    public function getCatalogueUrlByFiletype(string $format): string
    {
        return $this->getCatalogueUrl(['formats' => $format]);
    }

    public function getHardwareMap(): array
    {
        $tempMap = [];

        foreach ($this->hardwareRequired as $item) {
            $type = $this->getHardwareType($item);
            if ($type === null) {
                continue;
            }
            $tempMap[$type][] = $item;
        }

        $map = [];

        foreach ($this->getHardwareList() as $type => $items) {
            if (!empty($tempMap[$type])) {
                $map[$type] = $tempMap[$type];
            }
        }

        return $map;
    }

    public function getIconByHwType(string $hardwareType): ?string
    {
        return match ($hardwareType) {
            HardwareGroup::COMPUTERS->value => '🖥️',
            HardwareGroup::STORAGE->value => '💾',
            HardwareGroup::DOS->value => '🗂️',
            HardwareGroup::SOUND->value => '🔊',
            HardwareGroup::CONTROLS->value => '🎮',
            HardwareGroup::EXPANSION->value => '🛠️',
            default => null,
        };
    }


    public function getBreadcrumbsTitle(): string
    {
        $title = $this->getTitle();
        $fileInfo = $this->getCurrentReleaseFileInfo();
        if ($fileInfo !== null) {
            $title .= ' (' . $fileInfo['fileName'] . ')';
        }
        return $title;
    }

    public function getBreadcrumbsUrl(): string
    {
        $url = $this->getUrl();
        $fileInfo = $this->getCurrentReleaseFileInfo();
        if ($fileInfo !== null) {
            $url .= 'action:viewFile/id:' . $this->id . '/fileId:' . $fileInfo['id'] . '/';
        }
        return $url;
    }

    public function isBreadCrumb(): bool
    {
        return true;
    }

    #[Override]
    public function getMetaDescription(): string
    {
        $parts = [];
        $description = $this->cleanText($this->description ?? '');
        if ($description !== '') {
            $parts[] = $description;
        }
        $parts[] = $this->buildFactsString();

        return $this->limitText(implode(' ', $parts), 160);
    }

    #[Override]
    public function getTextContent(): string
    {
        $parts = [];
        $description = $this->cleanText($this->description ?? '');
        if ($description !== '') {
            $parts[] = $description;
        }
        $parts[] = $this->buildFactsString(true);

        return $this->limitText(implode(' ', $parts), 300);
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function limitText(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $text = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($text, ' ');
        if ($lastSpace !== false && $lastSpace > $limit * 0.8) {
            $text = mb_substr($text, 0, $lastSpace);
        }

        return rtrim($text, ' ,.-') . '...';
    }

    private function buildFactsString(bool $extended = false): string
    {
        $parts = [];
        $prod = $this->getProd();

        // Title
        $title = $this->title;
        if ($title === '' && $prod) {
            $title = $prod->title;
        }

        // Prod info
        $prodInfo = '';
        if ($prod) {
            $prodInfo = $prod->title;
            if ($prod->year) {
                $prodInfo .= ' (' . $prod->year . ')';
            }
        }

        if ($title !== '') {
            if ($prodInfo !== '' && $title !== $prod->title) {
                $parts[] = $title . ' — ' . $prodInfo;
            } else {
                $parts[] = $prodInfo ?: $title;
            }
        } elseif ($prodInfo !== '') {
            $parts[] = $prodInfo;
        }

        // Release facts
        $releaseFacts = [];
        if ($this->releaseType && $this->releaseType !== ReleaseTypes::unknown->value) {
            $releaseFacts[] = $this->releaseType;
        }
        if ($this->releaseFormat) {
            $releaseFacts[] = implode(', ', $this->releaseFormat);
        }
        if ($releaseFacts) {
            $parts[] = implode(', ', $releaseFacts);
        }

        if ($this->version) {
            $parts[] = 'v' . $this->version;
        }

        // Authors
        $authors = $this->getAuthorsInfo('release');
        if ($authors) {
            $authorNames = [];
            foreach (array_slice($authors, 0, 3) as $author) {
                if (isset($author['authorElement'])) {
                    $authorNames[] = $author['authorElement']->getTitle();
                }
            }
            if ($authorNames) {
                $parts[] = 'By ' . implode(', ', $authorNames);
            }
        }

        if ($this->year) {
            $parts[] = (string)$this->year;
        }

        $result = implode('. ', $parts);
        if ($result !== '') {
            $result .= '.';
        }

        return preg_replace('/\.+/', '.', $result);
    }
}