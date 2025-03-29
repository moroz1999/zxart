<?php

use ZxFiles\BasicFile;

/**
 * @property string $title
 * @property string $file
 * @property string $fileName
 * @property int $downloads
 * @property int $plays
 * @property string $year
 * @property string $releaseType
 * @property string $description
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
    Recalculable
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
    use ReleaseFormatsProvider;
    use ReleaseFileTypesGatherer;
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
    protected $prodElement;
    private const UspReleaseTypeRunnable = ['trd', 'tap', 'z80', 'sna', 'tzx', 'scl'];
    private const Zx81ReleaseTypeRunnable = ['tzx', 'p', 'o'];
    private const RunnableTypes = [...self::UspReleaseTypeRunnable, ...self::Zx81ReleaseTypeRunnable, ...self::TsconfReleaseTypeRunnable];
    private const Zx80HardwareRunnable = ["zx80"];
    private const Zx81HardwareRunnable = ["zx8116", "zx811", "zx812", "zx8132", "zx8164"];

    private const TsconfReleaseTypeRunnable = ['spg', 'img', 'trd', 'scl'];
    private const TsconfHardwareRunnable = ["tsconf"];

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
        $moduleStructure['year'] = 'text';
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
            /**
             * @var ZxParsingManager $zxParsingManager
             */
            $zxParsingManager = $this->getService('ZxParsingManager');
            return $zxParsingManager->getFileStructureById($this->id);
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
        return $controller->baseURL . 'release/id:' . $this->id . '/' . $this->getFileName();
    }

    public function getPlayUrl($serveZip = true): ?string
    {
        $controller = $this->getService('controller');
        if ($serveZip) {
            return $controller->baseURL . 'release/play:1/id:' . $this->id . '/' . $this->getFileName();
        }

        $playableFiles = $this->getPlayableFiles();
        $item = $playableFiles[0] ?? null;
        if ($item === null) {
            return null;
        }

        $fileName = $item['fileName'];
        return "{$controller->baseURL}zxfile/id:{$this->id}/fileId:{$item['id']}/play:1/{$fileName}";
    }

    public function getPlayableFiles(): array
    {
        $structure = $this->getReleaseFlatStructure();
        $playableFiles = [];
        foreach ($this->getRunnableTypes() as $runnableType) {
            foreach ($structure as $item) {
                $fileName = $item['fileName'];
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if ($extension === $runnableType) {
                    $playableFiles[] = $item;
                }
            }
        }

        return $playableFiles;
    }

    private function getRunnableTypes(): array
    {
        return match ($this->getEmulatorType()) {
            'tsconf' => self::TsconfReleaseTypeRunnable,
            'zx80' => self::Zx81ReleaseTypeRunnable,
            'zx81' => self::Zx81ReleaseTypeRunnable,
            'usp' => self::UspReleaseTypeRunnable,
        };

    }

    /**
     * @return string
     */
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

    /**
     * @return zxProdElement
     */
    public function getProd()
    {
        if ($this->prodElement === null) {
            $this->prodElement = false;
            if ($parent = $this->getFirstParentElement()) {
                if ($parent->structureType === 'zxProd') {
                    $this->prodElement = $parent;
                }
            }
            if ($parent = $this->getRequestedParentElement()) {
                if ($parent->structureType === 'zxProd') {
                    $this->prodElement = $parent;
                }
            }
        }

        return $this->prodElement;
    }

    public function getLegalStatus()
    {
        return $this->getProd()->getLegalStatus();
    }

    public function getReleaseTypes(): array
    {
        return [
            'unknown',
            'original',
            'rerelease',
            'adaptation',
            'localization',
            'mod',
            'crack',
            'mia',
            'corrupted',
            'compilation',
            'incomplete',
            'demoversion',
        ];
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
        /**
         * @var ZxParsingManager $zxParsingManager
         */
        $zxParsingManager = $this->getService('ZxParsingManager');
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
                        return "<img src='" . $controller->baseURL . "zxfile/id:" . $this->id . "/fileId:" . $fileId . "/" . $extractedFile->getItemName() . "' />";
                    }
                    break;
                case 'zx_basic':
                    $basic = new BasicFile();
                    $basic->setBinary($content);
                    return htmlspecialchars($basic->getAsText());
                case 'zx_image_standard':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:standard/' />";
                    }
                    break;
                case 'zx_image_monochrome':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:monochrome/' />";
                    }
                    break;
                case 'zx_image_tricolor':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:tricolor/' />";
                    }
                    break;
                case 'zx_image_gigascreen':
                    if ($fileId = $this->getFileId()) {
                        return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:gigascreen/' />";
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
                $zxParsingManager = $this->getService('ZxParsingManager');
                if ($fileInfo = $zxParsingManager->getFileRecord($fileId)) {
                    $this->currentReleaseFileInfo = $fileInfo;
                }
            }
        }
        return $this->currentReleaseFileInfo;
    }

    public function getReleaseFile(int $fileId): ?ZxParsingItem
    {
        /**
         * @var ZxParsingManager $zxParsingManager
         */
        $zxParsingManager = $this->getService('ZxParsingManager');
        if ($file = $zxParsingManager->extractFile($this->getFilePath(), $fileId)) {
            return $file;
        }

        return null;
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

    /**
     * @return string
     */
    public function getYear()
    {
        if (!empty($this->year)) {
            return $this->year;
        }
        if ($this->releaseType === 'original' && $zxProd = $this->getProd()) {
            if (!empty($zxProd->year)) {
                return $zxProd->year;
            }
        }
        return '';
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
        return $this->getEmulatorType() !== null;
    }

    public function isDownloadable(): bool
    {
        $user = $this->getService('user');
        $privileges = $this->getService('privilegesManager')->getElementPrivileges($this->id);

        return !in_array($this->getLegalStatus(), ['forbidden', 'forbiddenzxart', 'insales']) ||
            $this->releaseType === 'demoversion' ||
            !empty($privileges['zxRelease']['downloadDenied']) ||
            (
                ($this->getLegalStatus() !== 'insales') &&
                $user->isAuthorized() &&
                ($this->getProd()?->year !== 0) &&
                ($this->getProd()?->year < (date('Y') - 20))
            );
    }

    public function getLinksInfo()
    {
        if ($this->linksInfo === null) {
            $prod = $this->getProd();
            $this->linksInfo = [];
            $translationsManager = $this->getService('translationsManager');
            /**
             * @var \Illuminate\Database\Connection $db
             */
            $db = $this->getService('db');
            $query = $db->table('import_origin')
                ->select('importId', 'importOrigin')
                ->where('elementId', '=', $this->id)
                ->whereIn('importOrigin', ['vt', 'pouet']);
            if ($rows = $query->get()) {
                foreach ($rows as $row) {
                    if ($row['importOrigin'] == 'pouet') {
                        $this->linksInfo[] = [
                            'type' => 'pouet',
                            'image' => 'icon_pouet.png',
                            'name' => $translationsManager->getTranslationByName('links.link_pouet'),
                            'url' => 'https://www.pouet.net/prod.php?which=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] == 'vt' && $prod->getLegalStatus() !== 'insales') {
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

    public function getImageUrl($number)
    {
        if ($image = $this->getImage($number)) {
            return $image->getImageUrl('prodImage');
        } elseif ($zxProd = $this->getProd()) {
            return $zxProd->getImageUrl($number);
        } else {
            if ($number == 0) {
                $controller = $this->getService('controller');
                return $controller->baseURL . 'images/zxprod_default.png';
            }
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
        $computersList = array_intersect($this->hardwareRequired, $this->getHardwareList()['computers']);
        $dosList = array_intersect($this->hardwareRequired, $this->getHardwareList()['dos']);
        $translationsManager = $this->getService('translationsManager');

        $computersStrings = [];
        foreach ($computersList as $computer) {
            $computersStrings[] = $translationsManager->getTranslationByName('hardware.item_' . $computer);
        }

        $dosStrings = [];
        foreach ($dosList as $dos) {
            $dosStrings[] = $translationsManager->getTranslationByName('hardware.item_' . $dos);
        }

        $data['availableOnDevice'] = $computersStrings;
        $data['operatingSystem'] = $dosStrings;
        $data['applicationSubCategory'] = $this->getProd()->getCategoriesString();
        $data['applicationCategory'] = $this->getProd()->getRootCategoriesString();

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
    public function getMetaTitle()
    {
        $translationsManager = $this->getService('translationsManager');
        $title = $this->title;
        $title .= ' - ZX Spectrum release';

        if ($info = $this->getReleaseBy()) {
            $title .= ' by ';
            foreach ($info as $item) {
                $title .= $item->getTitle();
            }
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
                        'title' => $translationsManager->getTranslationByName('hardware_short.item_' . $item),
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
        $this->optimizeAliases('publishers');

        if (!$this->releaseType) {
            $this->releaseType = 'unknown';
        }
        parent::persistElementData(); // TODO: Change the autogenerated stub
    }

    public function updateFileStructure(): void
    {
        $this->parsed = 1;
        /**
         * @var ZxParsingManager $zxParsingManager
         */
        $zxParsingManager = $this->getService('ZxParsingManager');
        if ($structure = $zxParsingManager->updateFileStructure(
            $this->getId(),
            urldecode($this->getFilePath()),
            $this->fileName
        )) {
            if (!$this->releaseFormat) {
                if ($files = $this->gatherReleaseFiles($structure)) {
                    $files = array_unique($files);
                    $this->releaseFormat = $files;
                }
            }
        }
        $this->persistElementData();
    }

    /**
     * @return false|string
     */
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

    /**
     * @return void
     */
    public function recalculate()
    {
        $this->persistElementData();
    }

    public function incrementPlays(): void
    {
        $db = $this->getService('db');
        $db->table('module_zxrelease')->where('id', '=', $this->id)->limit(1)->increment('plays');
        $structureManager = $this->getService('structureManager');
        $structureManager->clearElementCache($this->id);
    }

    public function incrementDownloads(): void
    {
        $db = $this->getService('db');
        $db->table('module_zxrelease')->where('id', '=', $this->id)->limit(1)->increment('downloads');
        $structureManager = $this->getService('structureManager');
        $structureManager->clearElementCache($this->id);
    }

    public function getEmulatorType(): ?string
    {
        if (array_intersect($this->hardwareRequired, self::Zx80HardwareRunnable)) {
            foreach ($this->releaseFormat as $format) {
                if (in_array($format, self::Zx81ReleaseTypeRunnable, true)) {
                    return 'zx80';
                }
            }
        }
        if (array_intersect($this->hardwareRequired, self::Zx81HardwareRunnable)) {
            foreach ($this->releaseFormat as $format) {
                if (in_array($format, self::Zx81ReleaseTypeRunnable, true)) {
                    return 'zx81';
                }
            }
        }
        if (array_intersect($this->hardwareRequired, self::TsconfHardwareRunnable)) {
            return 'tsconf';
        }
        if ($this->releaseFormat) {
            foreach ($this->releaseFormat as $format) {
                if (in_array($format, self::UspReleaseTypeRunnable, true)) {
                    return 'usp';
                }
            }
        }
        return null;
    }

    public function getCanonicalUrl()
    {
        $url = parent::getCanonicalUrl();
        if ($this->actionName === 'viewFile') {
            $fileId = $this->getFileId();
            if ($fileId === null) {
                return $url;
            }
            $url .= "action:viewFile/id:{$this->id}/fileId:{$fileId}/";
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

    public function getIconByHwType(string $hardwareType): string
    {
        return match ($hardwareType) {
            'computers' => '🖥️',
            'storage' => '💾',
            'dos' => '🗂️',
            'sound' => '🔊',
            'controls' => '🎮',
            'expansion' => '🛠️',
            default => null,
        };
    }
}