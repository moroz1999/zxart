<?php

use ZxFiles\BasicFile;

/**
 * @property string $title
 * @property string $file
 * @property string $fileName
 * @property int $downloads
 * @property string $year
 * @property string $releaseType
 * @property string $description
 * @property string $version
 * @property array $hardwareRequired
 * @property array $language
 * @property array $releaseFormat
 * @property float $votes
 * @property int $denyVoting
 * @property int $denyComments
 * @property int $dateAdded
 * @property int $userId
 * @property int $parsed
 */
class zxReleaseElement extends ZxArtItem implements StructureElementUploadedFilesPathInterface, CommentsHolderInterface, JsonDataProvider, ZxSoftInterface, Recalculable
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
    protected $currentReleaseFile;
    protected $imagesUrls;
    protected $prodElement;
    protected static $textExtensions = [
        't', 'w', 'txt', 'bbs', 'me', 'nfo', 'nf0', 'diz', 'md', 'pok', 'd'
    ];
    protected static $sourceCodeExtensions = [
        'asm', 'a80', 'a', 'bat', 'cmd'
    ];

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

    public function getFileSelectorPropertyNames()
    {
        return ['screenshotsSelector', 'inlayFilesSelector', 'infoFilesSelector', 'adFilesSelector'];
    }

    public function getUploadedFilesPath($propertyName = 'connectedFile')
    {
        return $this->getService('PathsManager')->getPath('releases');
    }

    public function getFileUploadSuccessUrl()
    {
        return false;
    }

    public function isPrivilegesSettingRequired()
    {
        return true;
    }

    protected function getDeletionLinkTypes()
    {
        $result = ['structure'];
        foreach ($this->getFileSelectorPropertyNames() as $propertyName) {
            $result[] = $this->getConnectedFileType($propertyName);
        }
        return $result;
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
    }

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

    protected function fileExists($extensionType)
    {
        if (file_exists($this->getFilePath())) {
            return true;
        }
        return false;
    }

    public function getChartDataEventTypes($type = null)
    {
        return ['view'];
    }

    public function getReleaseStructure()
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
        if ($path = $this->getFilePath()) {
            /**
             * @var ZxParsingManager $zxParsingManager
             */
            $zxParsingManager = $this->getService('ZxParsingManager');
            if ($structure = $zxParsingManager->getFileStructureById($this->id)) {
                if (count($structure) > 100) {
                    $structure = array_slice($structure, 0, 100);
                }
                foreach ($structure as $key => $fileInfo) {
                    if ($file = $zxParsingManager->extractFile($this->getFilePath(), $fileInfo['id'])) {
                        $type = $this->getInternalFileType($fileInfo['fileName'], $fileInfo['type'], $fileInfo['size'], $file->getContent());
                        if ($type == 'binary') {
                            $structure[$key]['viewable'] = false;
                        } else {
                            $structure[$key]['viewable'] = true;
                        }
                        $structure[$key]['internalType'] = $type;
                    }
                }
            }
            return $structure;
        }
        return false;
    }

    public function getFilePath()
    {
        return $this->getUploadedFilesPath() . $this->file;
    }

    public function getFileUrl($play = false)
    {
        if ($play) {
            $url = controller::getInstance()->baseURL . 'release/play:1/id:' . $this->id . '/filename:' . $this->getFileName();
        } else {
            $url = controller::getInstance()->baseURL . 'release/id:' . $this->id . '/filename:' . $this->getFileName();
        }
        return $url;
    }

    public function getFileName(
        $extensionType = 'original',
        $escapeSpaces = true,
        $urlEncode = true,
        $addAuthor = true,
        $addYear = true,
        $addParty = true,
        $addPartyPlace = false,
        $addId = false
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

    public function getReleaseTypes()
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
        if ($file = $this->getCurrentReleaseFile()) {
            return $this->getFormattedFileContent($file);
        }
        return false;
    }

    public function getFormattedFileContent($file)
    {
        if ($fileType = $this->getInternalFileType('', $file->getItemExtension(), $file->getSize(), $file->getContent())) {
            if ($content = $file->getContent()) {
                switch ($fileType) {
                    case 'plain_text':
                    case 'source_code':
                        $content = EncodingDetector::decodeText($content);

                        return htmlspecialchars($content);
                    case 'pc_image':
                        $controller = controller::getInstance();
                        if ($fileId = (int)$controller->getParameter('fileId')) {
                            return "<img src='" . $controller->baseURL . "zxfile/id:" . $this->id . "/fileId:" . $fileId . "/" . $file->getItemName() . "' />";
                        }
                        break;
                    case 'zx_basic':

                        $basic = new BasicFile();
                        $basic->setBinary($content);
                        return htmlspecialchars($basic->getAsText());
                    case 'zx_image_standard':
                        $controller = controller::getInstance();
                        if ($fileId = (int)$controller->getParameter('fileId')) {
                            return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:standard/' />";
                        }
                        break;
                    case 'zx_image_monochrome':
                        $controller = controller::getInstance();
                        if ($fileId = (int)$controller->getParameter('fileId')) {
                            return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:monochrome/' />";
                        }
                        break;
                    case 'zx_image_tricolor':
                        $controller = controller::getInstance();
                        if ($fileId = (int)$controller->getParameter('fileId')) {
                            return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:tricolor/' />";
                        }
                        break;
                    case 'zx_image_gigascreen':
                        $controller = controller::getInstance();
                        if ($fileId = (int)$controller->getParameter('fileId')) {
                            return "<img src='" . $controller->baseURL . "zxFileScreen/id:" . $this->id . "/fileId:" . $fileId . "/type:gigascreen/' />";
                        }
                        break;
                    default:
                        $hex = new HexViewer();
                        return htmlspecialchars($hex->getFormatted($content));
                }
            }
        }
        return false;
    }

    protected function getInternalFileType($fileName, $extension, $size, $content)
    {
        if ($extension === 'file') {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        }
        if (in_array($extension, self::$textExtensions)) {
            $content = EncodingDetector::decodeText($content);
            if (!$content) {
                return 'binary';
            }
            return 'plain_text';
        } elseif (in_array($extension, self::$sourceCodeExtensions)) {
            return 'source_code';
        } elseif ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'bmp') {
            return 'pc_image';
        } elseif ($extension == 'b') {
            return 'zx_basic';
        } elseif ($size == 6912) {
            return 'zx_image_standard';
        } elseif ($size == 6144) {
            return 'zx_image_monochrome';
        } elseif ($size == 18432) {
            return 'zx_image_tricolor';
        } elseif ($size == 13824) {
            return 'zx_image_gigascreen';
        } else {
            return 'binary';
        }
    }

    public function getCurrentReleaseFile()
    {
        if ($this->currentReleaseFile === null) {
            $controller = controller::getInstance();
            if ($fileId = (int)$controller->getParameter('fileId')) {
                $this->currentReleaseFile = $this->getReleaseFile($fileId);
            }
        }
        return $this->currentReleaseFile;
    }

    public function getReleaseFile($fileId)
    {
        /**
         * @var ZxParsingManager $zxParsingManager
         */
        $zxParsingManager = $this->getService('ZxParsingManager');
        if ($file = $zxParsingManager->extractFile($this->getFilePath(), $fileId)) {
            return $file;
        }

        return false;
    }

    public function getFormData()
    {
        $test = parent::getFormData();
        return $test;
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

    public function getImagesList()
    {
        return array_merge($this->getFilesList('screenshotsSelector'), $this->getFilesList('inlayFilesSelector'), $this->getFilesList('adFilesSelector'));
    }

    public function isPlayable(): bool
    {
        if ($this->releaseFormat) {
            foreach ($this->releaseFormat as $format) {
                if (in_array($format, ['trd', 'tap', 'z80', 'sna', 'tzx', 'scl'])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isDownloadable()
    {
        $user = $this->getService('user');
        $privileges = $this->getService('privilegesManager')->getElementPrivileges($this->id);


        return
            !in_array($this->getLegalStatus(), ['forbidden', 'forbiddenzxart', 'insales']) ||
            $this->releaseType === 'demoversion' ||
            !empty($privileges['zxRelease']['downloadDenied']) ||
            $this->getLegalStatus() !== 'insales' && $user->isAuthorized() && $this->getProd()?->year !== 0 && $this->getProd()?->year < (date('Y') - 20);
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
                            'url' => 'http://www.pouet.net/prod.php?which=' . $row['importId'],
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

    public function getSearchTitle()
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

    public function getImage($number = 0)
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
//        $data = [
//            "@context" => "http://schema.org/",
//            "@type" => "VisualArtwork",
//            "name" => $this->title,
//            "url" => $this->URL,
//        ];
//        $data["description"] = $this->getTextContent();
//        if ($imageUrl = $this->getImageUrl(1, true)) {
//            $data['image'] = $imageUrl;
//        }
//        return $data;
        return false;
    }

    public function getBestPictures()
    {
        return [];
    }

    public function getReleaseBy()
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
    public function getImagesUrls($preset = 'prodImage')
    {
        $urls = [];
        foreach ($this->getFilesList('screenshotsSelector') as $fileElement) {
            $urls[] = $fileElement->getImageUrl($preset);
        }
        return $urls;
    }

    public function getPublishersInfo()
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
            if (($this->hardwareInfo = $this->getCacheKey($key)) === false) {
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

    public function persistElementData()
    {
        $this->optimizeAliases('publishers');

        if (!$this->releaseType) {
            $this->releaseType = 'unknown';
        }
        parent::persistElementData(); // TODO: Change the autogenerated stub
    }

    public function updateFileStructure()
    {
        $this->parsed = 1;
        /**
         * @var ZxParsingManager $zxParsingManager
         */
        $zxParsingManager = $this->getService('ZxParsingManager');
        $zxParsingManager->deleteFileStructure($this->getId());
        if ($structure = $zxParsingManager->saveFileStructure(
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

    public function getCompilationJsonData()
    {
        $data = [
            'compilations' => [],
        ];
        foreach ($this->compilations as $prod) {
            $data['compilations'][] = $prod->getElementData('list');
        }
        return json_encode($data);
    }

    public function recalculate()
    {
        $this->persistElementData();
    }

    public function incrementPlays()
    {
        $db = $this->getService('db');
        $db->table('module_zxrelease')->where('id', '=', $this->id)->limit(1)->increment('plays');
        $structureManager = $this->getService('structureManager');
        $structureManager->clearElementCache($this->id);
    }

    public function incrementDownloads()
    {
        $db = $this->getService('db');
        $db->table('module_zxrelease')->where('id', '=', $this->id)->limit(1)->increment('downloads');
        $structureManager = $this->getService('structureManager');
        $structureManager->clearElementCache($this->id);
    }
}