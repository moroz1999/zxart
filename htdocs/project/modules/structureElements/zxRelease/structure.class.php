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
class zxReleaseElement extends ZxArtItem implements StructureElementUploadedFilesPathInterface, CommentsHolderInterface
{
    use AuthorshipProviderTrait;
    use AuthorshipPersister;
    use ImportedItemTrait;
    use HardwareProviderTrait;
    use LanguageCodesProviderTrait;
    use LinksPersistingTrait;
    use PublisherGroupProviderTrait;
    use MaterialsProviderTrait;
    use FilesElementTrait;
    use ReleaseFormatsProvider;
    use GalleryInfoProviderTrait;

    public $dataResourceName = 'module_zxrelease';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $viewName = 'short';

    protected $votesType = 'zxRelease';
    protected $partyLinkType = 'partyProd';

    protected $currentReleaseFile;
    protected $imagesUrls;
    protected $prodElement;
    protected static $textExtensions = [
        't', 'w', 'txt', 'asm', 'a80', 'bbs', 'me', 'd', 'nfo', 'nf0', 'diz', 'a',
    ];

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';

        $moduleStructure['file'] = 'file';
        $moduleStructure['fileName'] = 'text';

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
        $moduleStructure['description'] = 'pre';
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

    public function getElementData()
    {
        // generic
        $data["id"] = $this->id;
        $data["title"] = $this->title;
        $data["link"] = $this->URL;
        $data["votes"] = $this->votes;
        $data["userVote"] = $this->getUserVote();
        $data["votePercent"] = $this->getVotePercent();
        return $data;
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
        if ($path = $this->getFilePath()) {
            /**
             * @var ZxParsingManager $zxParsingManager
             */
            $zxParsingManager = $this->getService('ZxParsingManager');
            if ($structure = $zxParsingManager->getFileStructureById($this->id)) {
                foreach ($structure as $key => $fileInfo) {
                    $type = $this->getInternalFileType($fileInfo['fileName'], $fileInfo['type'], $fileInfo['size']);
                    if ($type == 'binary') {
                        $structure[$key]['viewable'] = false;
                    } else {
                        $structure[$key]['viewable'] = true;
                    }
                }

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
            if ($fileType = $this->getInternalFileType('', $file->getItemExtension(), $file->getSize())) {
                if ($content = $file->getContent()) {
                    switch ($fileType) {
                        case 'plain_text':
                            return '<pre>' . htmlspecialchars($content) . '</pre>';
                        case 'cp866_text':
                            return '<pre>' . htmlspecialchars(
                                    mb_convert_encoding($content, 'UTF-8', 'CP866')
                                ) . '</pre>';

                        case 'pc_image':
                            $controller = controller::getInstance();
                            if ($fileId = (int)$controller->getParameter('fileId')) {
                                return "<img src='" . $controller->baseURL . "zxfile/id:" . $this->id . "/fileId:" . $fileId . "/" . $file->getItemName() . "' />";
                            }
                            break;
                        case 'zx_basic':

                            $basic = new BasicFile();
                            $basic->setBinary($content);
                            return '<pre>' . htmlspecialchars($basic->getAsText()) . '</pre>';
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
                            return '<pre>' . htmlspecialchars($hex->getFormatted($content)) . '</pre>';
                    }
                }
            }
        }
        return false;
    }

    protected function getInternalFileType($fileName, $extension, $size)
    {
        if ($extension === 'file') {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        }
        if ($extension == 'pok' || $extension == 'diz' || $extension == 'nfo') {
            return 'plain_text';
        } elseif (in_array($extension, self::$textExtensions)) {
            return 'cp866_text';
        } elseif ($extension == 'jpg' || $extension == 'png' || $extension == 'bmp') {
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
        if ($zxProd = $this->getProd()) {
            return $zxProd->year;
        }
        return false;
    }

    public function getImagesList()
    {
        return array_merge($this->getFilesList('inlayFilesSelector'), $this->getFilesList('adFilesSelector'));
    }

    public function isPlayable()
    {
        return $this->releaseFormat && !array_diff($this->releaseFormat, ['trd', 'tap', 'z80', 'sna', 'tzx', 'scl']);
    }

    public function isDownloadable()
    {
        return !in_array($this->getLegalStatus(), ['forbidden', 'forbiddenzxart', 'insales']) || $this->releaseType === 'demoversion';
    }

    public function getLinksInfo()
    {
        if ($this->linksInfo === null) {
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
                    } elseif ($row['importOrigin'] == 'vt') {
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
                return $controller->baseURL . 'project/images/public/zxprod_default.png';
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
}