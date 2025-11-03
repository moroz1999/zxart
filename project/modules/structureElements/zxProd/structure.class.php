<?php

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatusProvider;
use ZxArt\Queue\QueueType;
use ZxArt\ZxProdCategories\CategoryIds;
use ZxArt\ZxProdCategories\CompilationCategoryIds;

/**
 * Class zxProdElement
 *
 * @property string $title
 * @property string $altTitle
 * @property int $year
 * @property string $youtubeId
 * @property string $description
 * @property string $instructions
 * @property string $legalStatus
 * @property string $compo
 * @property string $tagsText
 * @property string[] $language
 * @property string $externalLink
 * @property int $party
 * @property int $commentsAmount
 * @property int $votesAmount
 * @property int[] $categories
 * @property groupElement[] $publishers
 * @property groupElement[] $groups
 * @property zxProdElement[] $compilationItems
 * @property zxProdElement[] $seriesProds
 * @property zxProdElement[] $compilations
 * @property zxProdElement[] $series
 * @property pressArticleElement[] $articles
 * @property float $votes
 * @property int $partyplace
 * @property int $denyVoting
 * @property int $denyComments
 * @property int $dateAdded
 * @property int $userId
 * @property int $joinAndDelete
 * @property boolean $releasesOnly
 * @property array[] $splitData
 * @property boolean $aiRestartSeo
 * @property boolean $aiRestartIntro
 * @property boolean $aiRestartCategories
 */
class zxProdElement extends ZxArtItem implements
    StructureElementUploadedFilesPathInterface,
    CommentsHolderInterface,
    JsonDataProvider,
    OpenGraphDataProviderInterface,
    ZxSoftInterface,
    MetadataProviderInterface
{
    use QueueStatusProvider;
    use AuthorshipProviderTrait;
    use AuthorshipPersister;
    use FilesElementTrait;
    use ImportedItemTrait;
    use PartyElementProviderTrait;
    use LanguageCodesProviderTrait;
    use CategoryElementsSelectorProviderTrait;
    use LinksPersistingTrait;
    use PublisherGroupProviderTrait;
    use MaterialsProviderTrait;
    use GalleryInfoProviderTrait;
    use DemoCompoTypesProvider;
    use JsonDataProviderElement;
    use ZxSoft;

    public $dataResourceName = ProdsRepository::TABLE;
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $viewName = 'details';

    protected $votesType = 'zxProd';
    protected $partyLinkType = 'partyProd';
    /**
     * @var zxReleaseElement[]
     */
    protected $releasesList;
    protected $images = [];
    protected $bestPictures;
    protected $languagesInfo;
    protected $hardwareInfo;
    private $metaData;
    protected $sectionType = 'software';

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['altTitle'] = 'text';

        $moduleStructure['party'] = 'text';
        $moduleStructure['partyplace'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['year'] = 'naturalNumber';
        $moduleStructure['youtubeId'] = 'text';
        $moduleStructure['description'] = 'pre';
        $moduleStructure['instructions'] = 'html';
        $moduleStructure['votes'] = 'floatNumber';

        $moduleStructure['categories'] = 'numbersArray';
        $moduleStructure['publishers'] = [
            'ConnectedElements',
            [
                'linkType' => 'zxProdPublishers',
                'role' => 'child',
            ],
        ];
        $moduleStructure['groups'] = [
            'ConnectedElements',
            [
                'linkType' => 'zxProdGroups',
                'role' => 'child',
            ],
        ];
        $moduleStructure['articles'] = [
            'ConnectedElements',
            [
                'linkType' => 'prodArticle',
                'role' => 'parent',
            ],
        ];
        $moduleStructure['tagsText'] = 'text';
        $moduleStructure['tagsAmount'] = 'text';
        $moduleStructure['votesAmount'] = 'text';
        $moduleStructure['commentsAmount'] = 'text';
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';

        $moduleStructure['addAuthor'] = 'text';
        $moduleStructure['addAuthorRole'] = 'array';

        $moduleStructure['dateAdded'] = 'date';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['legalStatus'] = 'text';
        $moduleStructure['externalLink'] = 'url';
        $moduleStructure['language'] = [
            'DBValueSet',
            [
                'tableName' => 'zxitem_language',
            ],
        ];
        $moduleStructure['compilationItems'] = [
            'ConnectedElements',
            [
                'linkType' => 'compilation',
                'role' => 'parent',
            ],
        ];
        $moduleStructure['seriesProds'] = [
            'ConnectedElements',
            [
                'linkType' => 'series',
                'role' => 'parent',
            ],
        ];
        $moduleStructure['compilations'] = [
            'ConnectedElements',
            [
                'linkType' => 'compilation',
                'role' => 'child',
            ],
        ];
        $moduleStructure['series'] = [
            'ConnectedElements',
            [
                'linkType' => 'series',
                'role' => 'child',
            ],
        ];
        $moduleStructure['joinAndDelete'] = 'text';
        $moduleStructure['releasesOnly'] = 'checkbox';
        $moduleStructure['splitData'] = 'array';
        $moduleStructure['mentions'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_SOFTWARE->value,
                'role' => 'parent',
            ],
        ];
        $moduleStructure['aiRestartSeo'] = 'checkbox';
        $moduleStructure['aiRestartIntro'] = 'checkbox';
        $moduleStructure['aiRestartCategories'] = 'checkbox';
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'connectedFile', 'inlayFilesSelector', 'mapFilesSelector', 'rzx'}
     */
    public function getFileSelectorPropertyNames(): array
    {
        return ['connectedFile', 'inlayFilesSelector', 'mapFilesSelector', 'rzx'];
    }

    /**
     * @return int
     */
    public function getPartyId()
    {
        return $this->party;
    }

    /**
     * @return string
     *
     * @psalm-return ''
     */
    public function getFileExtension($extensionType)
    {
        $extension = '';
        return $extension;
    }

    /**
     * @return false
     */
    protected function fileExists($extensionType)
    {
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
     * @return zxReleaseElement[]
     */
    public function getReleasesList($forceUpdate = false)
    {
        if ($forceUpdate || $this->releasesList === null) {
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            $this->releasesList = $structureManager->getElementsChildren($this->getId());
            usort($this->releasesList, function ($a, $b) {
                $aYear = !$a->getYear() ? 3000 : $a->getYear();
                $bYear = !$b->getYear() ? 3000 : $b->getYear();
                $c1 = ($aYear - $bYear);
                $c2 = (($a->releaseType === 'original') && $b->releaseType !== 'original') ? -1 : ((($a->releaseType !== 'original') && $b->releaseType === 'original') ? 1 : 0);
                $c3 = strcmp($a->version, $b->version);
                $c4 = ($a->id - $b->id);

                return $c1 !== 0 ? $c1 : ($c2 !== 0 ? $c2 : ($c3 !== 0 ? $c3 : $c4));
            });
        }
        return $this->releasesList;
    }

    /**
     * @return int[]
     */
    public function getReleasesIds()
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService('linksManager');
        return $linksManager->getConnectedIdList($this->getId(), 'structure', 'parent');
    }

    public function getUploadedFilesPath($propertyName = 'default')
    {
        return $this->getService('PathsManager')->getPath('releases');
    }

    public function getImage($number = 0)
    {
        $result = $this->getFilesList('connectedFile');
        if (isset($result[$number])) {
            return $result[$number];
        }
        foreach ($this->getReleasesList() as $releaseElement) {
            $result = array_merge($result, $releaseElement->getFilesList('screenshotsSelector'));
        }
        if (isset($result[$number])) {
            return $result[$number];
        }
        foreach ($this->compilationItems as $prod) {
            if ($image = $prod->getImage($number)) {
                return $image;
            }
        }

        return false;
    }

    //used in API

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getImagesUrls($preset = 'prodImage'): array
    {
        $urls = [];
        foreach ($this->getFilesList('connectedFile') as $fileElement) {
            $url = $fileElement->getImageUrl($preset);
            $urls[] = str_replace('http://zxart.loc', 'https://zxart.ee', $url);
        }
        return $urls;
    }


    public function getInlaysUrls()
    {
        if (($urls = $this->getCacheKey('inlays')) === null) {
            $db = $this->getService('db');
            /**
             * @var QueryFiltersManager $queryFiltersManager
             */
            $releaseIdsQuery = $db->table($this->dataResourceName)->where('id', $this->getId());

            $queryFiltersManager = $this->getService('QueryFiltersManager');
            $releaseIdsQuery = $queryFiltersManager->convertTypeData($releaseIdsQuery, 'zxRelease', 'zxProd', [])->select('id');
            $urls = [];

            foreach ($this->getFilesList('inlayFilesSelector') as $fileElement) {
                $urls[] = $fileElement->getImageUrl('prodListInlay');
            }

            if ($imageIds = $db->table('structure_links')
                ->whereIn('parentStructureId', $releaseIdsQuery)
                ->whereIn('type', ['inlayFilesSelector', 'adFilesSelector'])
                ->pluck('childStructureId')
            ) {
                $controller = $this->getService('controller');
                foreach ($imageIds as $imageId) {
                    $urls[] = $controller->baseURL . 'image/type:prodListInlay/id:' . $imageId;
                }
                $this->setCacheKey('inlays', $urls, 3600 * 24);
            }
        }

        return $urls;
    }

    public function getImageUrl($number = 0)
    {
        if ($image = $this->getImage($number)) {
            return $image->getImageUrl('prodImage');
        }

        if ($number === 0) {
            $controller = $this->getService('controller');
            if ($this->legalStatus === LegalStatus::unreleased->name) {
                return $controller->baseURL . 'images/zxprod_unreleased.png';
            }

            if ($this->legalStatus === LegalStatus::mia->name) {
                return $controller->baseURL . 'images/zxprod_mia.png';
            }
            return $controller->baseURL . 'images/zxprod_default.png';
        }
        return false;
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'structure', string}
     */
    protected function getDeletionLinkTypes(): array
    {
        return ['structure', $this->getConnectedFileType()];
    }

    private function checkSeriesCategories(): void
    {
        if (!$this->seriesProds || $this->getReleasesList()) {
            return;
        }
        $rootCategories = $this->getRootCategories();
        $updateRequired = false;
        foreach ($rootCategories as $category) {
            if ($category->id !== CategoryIds::SERIES->value) {
                $updateRequired = true;
            }
        }
        if (!$updateRequired) {
            return;
        }
        $this->categories = [CategoryIds::SERIES->value];
        $this->checkAndPersistCategories();
    }

    private function checkCompilationCategories(): void
    {
        return;
        if (!$this->compilationItems) {
            return;
        }
        $compilationCategoryIds = [];
        $rootCategories = $this->getRootCategories();
        $isPress = false;
        foreach ($rootCategories as $category) {
            $compiletionCategoryId = CompilationCategoryIds::getCompilationCategoryId($category->id);
            if ($compiletionCategoryId !== null) {
                $compilationCategoryIds[] = $compiletionCategoryId;
            }
            if ($category === CategoryIds::PRESS) {
                $isPress = true;
            }
        }

        if ($compilationCategoryIds === []) {
            $compilationCategoryIds = [CategoryIds::COMPILATION_GAMES->value];
        }
        $this->categories = $compilationCategoryIds;
        $this->checkAndPersistCategories();
    }

    /**
     * @return void
     */
    public function persistElementData()
    {
        $this->checkCompilationCategories();
        $this->checkSeriesCategories();
        if (!$this->hasActualStructureInfo()) {
            $this->dateAdded = time();
        }
        if (!$this->legalStatus) {
            $this->legalStatus = 'unknown';
        }
        $this->optimizeAliases('groups');
        $this->optimizeAliases('publishers');

        parent::persistElementData();

        $structureManager = $this->getService('structureManager');
        if ($elements = $structureManager->getElementsByType('zxItemsList')) {
            foreach ($elements as $element) {
                if ($element->items = 'zxProd') {
                    $structureManager->clearElementCache($element->getId());
                }
            }
        }
        foreach ($this->categories as $categoryId) {
            $structureManager->clearElementCache($categoryId);
        }

        // for all prods created from import, mass-upload, ensure SEO and intro creation
        $queueService = $this->getService(QueueService::class);
        $queueService->checkElementInQueue($this->getPersistedId(), [QueueType::AI_SEO, QueueType::AI_INTRO]);
    }

    public function getBestPictures($limit = false, $excludeId = null)
    {
        if ($this->bestPictures === null) {
            /**
             * @var ApiQueriesManager $queriesManager
             */
            $queriesManager = $this->getService('ApiQueriesManager');

            $sort = ['votes' => 'desc'];
            $parameters = [
                'zxProdId' => [$this->getId()],
                'zxPictureNotId' => $excludeId,
                'zxPictureMinRating' => $this->getService('ConfigManager')->get('zx.averageVote'),
            ];

            $query = $queriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setExportType('zxPicture');
            $query->setOrder($sort);
            $query->setStart(0);
            $query->setLimit(10);

            if ($result = $query->getQueryResult()) {
                shuffle($result['zxPicture']);
                $this->bestPictures = array_slice($result['zxPicture'], 0, $limit);
            }
        }
        return $this->bestPictures;
    }

    public function getLegalStatus(): string
    {
        if ($this->legalStatus) {
            return $this->legalStatus;
        }
        return 'unknown';
    }

    public function getConnectedCategoriesIds()
    {
        if ($this->connectedCategoriesIds === null) {
            $this->connectedCategoriesIds = $this->getService('linksManager')
                ->getConnectedIdList($this->getId(), 'zxProdCategory', 'child');
            if (!$this->connectedCategoriesIds) {
                if ($element = $this->getFirstParentElement()) {
                    if ($element->structureType === 'zxProdCategory') {
                        $this->connectedCategoriesIds = [$element->getId()];
                    }
                }
            }
        }
        return $this->connectedCategoriesIds;
    }

    /**
     * @return string[]
     *
     * @psalm-return array<string>
     */
    public function getSupportedLanguageCodes()
    {
        return $this->language;
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
     * @return (fileElement|mixed)[]
     *
     * @psalm-return array<fileElement|mixed>
     */
    public function getImagesList()
    {
        $result = [];
        $result = array_merge($result, $this->getFilesList('connectedFile'));

        foreach ($this->getReleasesList() as $releaseElement) {
            $result = array_merge($result, $releaseElement->getImagesList());
        }
        $result = array_merge($result, $this->getFilesList('inlayFilesSelector'));
        $result = array_merge($result, $this->getFilesList('mapFilesSelector'));

        return $result;
    }

    public function getLinkInfo($type): ?array
    {
        foreach ($this->getLinksInfo() as $linkInfo) {
            if ($linkInfo['type'] === $type) {
                return $linkInfo;
            }
        }
        return null;
    }

    public function getSpeccyMapsUrl(): ?string
    {
        $linkInfo = $this->getLinkInfo('maps');
        return $linkInfo['url'] ?? null;
    }

    public function getLinksInfo(): array
    {
        if ($this->linksInfo === null) {
            $this->linksInfo = [];
            $translationsManager = $this->getService('translationsManager');
            /**
             * @var Connection $db
             */
            $db = $this->getService('db');

            if ($this->is3aDenied()) {
                $types = ['zxdb', 'vt', 'dzoo', 'pouet', 'zxd', 'maps'];
            } else {
                $types = ['3a', 'zxdb', 'vt', 'dzoo', 'pouet', 'zxd', 'maps', 'worldofsam'];
            }

            $query = $db->table('import_origin')
                ->select('importId', 'importOrigin')
                ->where('elementId', '=', $this->getId())
                ->whereIn('importOrigin', $types);
            if ($rows = $query->get()) {
                foreach ($rows as $row) {
                    if ($row['importOrigin'] === 'zxdb') {
                        if (str_contains($row['importId'], 'tag')) {
                            $url = 'https://spectrumcomputing.co.uk/list?group_id=' . substr($row['importId'], 3);
                        } else {
                            $url = 'https://spectrumcomputing.co.uk/index.php?cat=96&id=' . $row['importId'];
                        }

                        $this->linksInfo[] = [
                            'type' => 'zxdb',
                            'image' => 'icon_sc.png',
                            'name' => $translationsManager->getTranslationByName('links.link_sc'),
                            'url' => $url,
                            'id' => $row['importId'],
                        ];
                        if ($row['importId'] < 28188) {
                            $row['importId'] = sprintf("%07d", $row['importId']);
                            $this->linksInfo[] = [
                                'type' => 'wos',
                                'image' => 'icon_wos.png',
                                'name' => $translationsManager->getTranslationByName('links.link_wos'),
                                'url' => 'https://worldofspectrum.org/software?id=' . $row['importId'],
                                'id' => $row['importId'],
                            ];
                        }
                    } elseif ($row['importOrigin'] === '3a') {
                        $this->linksInfo[] = [
                            'type' => '3a',
                            'image' => 'icon_3a.png',
                            'name' => $translationsManager->getTranslationByName('links.link_3a'),
                            'url' => 'https://zxaaa.net/view_demo.php?id=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'pouet') {
                        $this->linksInfo[] = [
                            'type' => 'pouet',
                            'image' => 'icon_pouet.png',
                            'name' => $translationsManager->getTranslationByName('links.link_pouet'),
                            'url' => 'https://www.pouet.net/prod.php?which=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'dzoo') {
                        $this->linksInfo[] = [
                            'type' => 'dzoo',
                            'image' => 'icon_dzoo.png',
                            'name' => $translationsManager->getTranslationByName('links.link_dzoo'),
                            'url' => 'https://demozoo.org/productions/' . $row['importId'] . '/',
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'zxd') {
                        $this->linksInfo[] = [
                            'type' => 'zxd',
                            'image' => 'icon_zxd.png',
                            'name' => $translationsManager->getTranslationByName('links.link_zxd'),
                            'url' => 'https://zxdemo.org/productions/' . $row['importId'] . '/',
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'maps') {
                        $this->linksInfo[] = [
                            'type' => 'maps',
                            'image' => 'icon_maps.png',
                            'name' => $translationsManager->getTranslationByName('links.link_maps'),
                            'url' => 'https://maps.speccy.cz/map.php?id=' . $row['importId'] . '&sort=0&part=0&ath=0',
                            'id' => $row['importId'],
                        ];
                    }elseif ($row['importOrigin'] === 'worldofsam') {
                        $this->linksInfo[] = [
                            'type' => 'worldofsam',
                            'image' => 'icon_worldofsam.png',
                            'name' => $translationsManager->getTranslationByName('links.link_worldofsam'),
                            'url' => 'https://www.worldofsam.org/products/' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    }
                }
            }
        }
        foreach ($this->getReleasesList() as $release) {
            if ($linksInfo = $release->getLinksInfo()) {
                foreach ($linksInfo as $linkInfo) {
                    if ($linkInfo['type'] == 'vt') {
                        $this->linksInfo[] = $linkInfo;
                        goto end;
                    }
                }
            }
        }

        end:
        return $this->linksInfo;
    }

    public function getSearchTitle(): string
    {
        $searchTitle = $this->title;
        /**
         * @var translationsManager $translationsManager
         */
        $translationsManager = $this->getService('translationsManager');
        if ($this->seriesProds) {
            $searchTitle = $translationsManager->getTranslationByName('zxprod.seriesprod') . ': ' . $searchTitle;
        }
        if ($this->compilationItems) {
            $searchTitle = $translationsManager->getTranslationByName('zxprod.compilation') . ': ' . $searchTitle;
        }

        if ($this->year) {
            $searchTitle .= ' (' . $this->year . ')';
        }
        $groups = [];
        foreach ($this->groups as $group) {
            $groups[] = $group->getTitle();
        }
        if ($groups) {
            $searchTitle .= ' / ' . implode(', ', $groups);
        }

        return $searchTitle;
    }

    public function is3aDenied(): bool
    {
        if ($authors = $this->getAuthorsInfo('prod')) {
            foreach ($authors as $author) {
                if (method_exists($author['authorElement'], 'is3aDenied')) {
                    if ($author['authorElement']->is3aDenied()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getSplitData(): array
    {
        $data = [];
        $properties = ['title', 'year', 'youtube'];
        foreach ($properties as $property) {
            if ($this->$property) {
                $data['properties'][$property] = $this->$property;
            }
        }
        foreach ($this->getAuthorsInfo('prod') as $authorInfo) {
            if ($authorElement = $authorInfo['authorElement']) {
                $data['authors'][$authorInfo['id']] = $authorElement->getId() . ' <a target="_blank" href="' . $authorElement->getUrl() . '">' . $authorElement->getSearchTitle() . '</a>';
            }
        }
        foreach ($this->publishers as $publisher) {
            $data['publishers'][$publisher->id] = $publisher->id . ' <a target="_blank" href="' . $publisher->getUrl() . '">' . $publisher->getSearchTitle() . '</a>';
        }
        foreach ($this->groups as $group) {
            $data['groups'][$group->id] = $group->id . ' <a target="_blank" href="' . $group->getUrl() . '">' . $group->getSearchTitle() . '</a>';
        }
        foreach ($this->getReleasesList() as $releaseElement) {
            $data['releases'][$releaseElement->getId()] = $releaseElement->getId() . ' <a target="_blank" href="' . $releaseElement->getUrl() . '">' . $releaseElement->getSearchTitle() . '</a>';
        }
        foreach ($this->getFilesList('connectedFile') as $fileElement) {
            $data['screenshots'][$fileElement->getId()] = $fileElement->getId() . ' <img style="height: 5rem" src="' . $fileElement->getImageUrl('prodImage') . '" />';
        }

        foreach ($this->getLinksInfo() as $linkInfo) {
            if ($linkInfo['type'] !== 'wos') {
                $data['links'][$linkInfo['type'] . ';' . $linkInfo['id']] = $linkInfo['type'] . ' <a target="_blank" href="' . $linkInfo['url'] . '">' . $linkInfo['id'] . '</a>';;
            }
        }

        return $data;
    }

    public function getLdJsonScriptData()
    {
        $data = [
            "@context" => "http://schema.org/",
            "@type" => ["SoftwareApplication"],
            "name" => $this->title,
            "url" => $this->URL,
        ];
        $releases = $this->getReleasesList();
        $release = $releases[0] ?? null;
        if ($release !== null) {
            $computersList = array_intersect($release->hardwareRequired, $release->getHardwareList()['computers']);
            $dosList = array_intersect($release->hardwareRequired, $release->getHardwareList()['dos']);
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

            if ($release->isDownloadable()) {
                $data['downloadUrl'] = $release->getFileUrl();
            }
        }

        $data['applicationSubCategory'] = $this->getCategoriesString();
        $data['applicationCategory'] = $this->getRootCategoriesString();

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


        if ($tags = $this->generateTagsText()) {
            $data['keywords'] = $tags;
        }
        return $data;
    }

    public function getHardware()
    {
        $db = $this->getService('db');
        /**
         * @var QueryFiltersManager $queryFiltersManager
         */
        $query = $db->table($this->dataResourceName)->where('id', $this->getId());

        $queryFiltersManager = $this->getService('QueryFiltersManager');
        $query = $queryFiltersManager->convertTypeData($query, 'zxRelease', 'zxProd', [])->select('id');
        $hwItems = $db->table('module_zxrelease_hw_required')
            ->whereIn('elementId', $query)
            ->distinct()
            ->pluck('value');
        return $hwItems;
    }


    public function getHardwareInfo(bool $short = true)
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
                foreach ($this->getHardware() as $item) {
                    $this->hardwareInfo[] = [
                        'id' => $item,
                        'title' => $translationsManager->getTranslationByName($short ? 'hardware_short.item_' . $item : 'hardware.item_' . $item),
                    ];
                }
                $this->setCacheKey($key, $this->hardwareInfo, 24 * 60 * 60);
            }
        }
        return $this->hardwareInfo;
    }

    public function getPublishersInfo(): array
    {
        $publishersInfo = [];
        foreach ($this->publishers as $publisher) {
            $publishersInfo[] = [
                'id' => $publisher->getId(),
                'title' => html_entity_decode($publisher->title, ENT_QUOTES),
                'url' => $publisher->getUrl(),
            ];
        }
        return $publishersInfo;
    }

    public function getGroupsInfo(): array
    {
        $groupsInfo = [];
        foreach ($this->groups as $group) {
            $groupsInfo[] = [
                'id' => $group->getId(),
                'title' => html_entity_decode($group->title, ENT_QUOTES),
                'url' => $group->getUrl(),
            ];
        }
        return $groupsInfo;
    }

    public function getLanguagesInfo()
    {
        if (!isset($this->languagesInfo)) {
            if (($this->languagesInfo = $this->getCacheKey('li' . $this->currentLanguage)) === null) {
                $this->languagesInfo = [];

                $db = $this->getService('db');
                /**
                 * @var QueryFiltersManager $queryFiltersManager
                 */
                $query = $db->table($this->dataResourceName)->where('id', $this->getId());

                $queryFiltersManager = $this->getService('QueryFiltersManager');
                $query = $queryFiltersManager->convertTypeData($query, 'zxRelease', 'zxProd', [])->select('id');
                $languageCodes = $db->table('zxitem_language')
                    ->whereIn('elementId', $query)
                    ->orWhere('elementId', $this->getId())
                    ->distinct()
                    ->pluck('value');
                /**
                 * @var translationsManager $translationsManager
                 */
                $translationsManager = $this->getService('translationsManager');

                foreach ($languageCodes as $languageCode) {
                    $this->languagesInfo[] = [
                        'id' => $languageCode,
                        'title' => $translationsManager->getTranslationByName('language.item_' . $languageCode),
                        'url' => null,
                    ];
                }
                $this->setCacheKey('li' . $this->currentLanguage, $this->languagesInfo, 24 * 60 * 60);
            }
        }

        return $this->languagesInfo;
    }

    private function makeCategoryInfo($category): array
    {
        return [
            'id' => $category->id,
            'title' => html_entity_decode($category->title, ENT_QUOTES),
            'url' => $category->getUrl(),
        ];
    }

    public function getCategoriesInfo(): array
    {
        $categoriesInfo = [];
        foreach ($this->getConnectedCategories() as $category) {
            $categoriesInfo[] = $this->makeCategoryInfo($category);
        }
        return $categoriesInfo;
    }

    public function getRootCategoriesInfo(): array
    {
        $categoriesInfo = [];
        foreach ($this->getRootCategories() as $category) {
            $categoriesInfo[] = $this->makeCategoryInfo($category);
        }
        return $categoriesInfo;
    }

    public function getCategoriesString(): string
    {
        $categoriesNames = [];
        foreach ($this->getConnectedCategories() as $category) {
            $categoriesNames[] = html_entity_decode($category->title, ENT_QUOTES);
        }
        return implode(', ', array_unique($categoriesNames));
    }

    public function getRootCategories(): array
    {
        $rootCategories = [];
        foreach ($this->getConnectedCategories() as $category) {
            $rootCategory = $category->getRootCategory();
            if (($rootCategory !== null) && !in_array($rootCategory, $rootCategories, true)) {
                $rootCategories[] = $rootCategory;
            }
        }
        return $rootCategories;
    }


    public function getRootCategoriesString(): string
    {
        $categoriesNames = [];
        foreach ($this->getRootCategories() as $rootCategory) {
            $categoriesNames[] = html_entity_decode($rootCategory->title, ENT_QUOTES);
        }
        return implode(', ', array_unique($categoriesNames));
    }

    /**
     * @return zxProdCategoryElement[][]
     */
    public function getCategoriesPaths(): array
    {
        $usedCategories = [];
        $paths = [];
        foreach ($this->getConnectedCategories() as $category) {
            if (!isset($usedCategories[$category->id])) {
                $path = [$category];

                $usedCategories[$category->id] = true;
                while ($parentCategory = $category->getParentCategory()) {
                    $category = $parentCategory;

                    $usedCategories[$parentCategory->id] = true;
                    array_unshift($path, $parentCategory);
                }
                $paths[] = $path;
            }
        }
        return $paths;
    }

    public function getParentCategoriesMap(): array
    {
        $usedCategories = [];
        foreach ($this->getConnectedCategories() as $category) {
            if (!isset($usedCategories[$category->id])) {
                $usedCategories[$category->id] = true;
                while ($parentCategory = $category->getParentCategory()) {
                    $category = $parentCategory;
                    $usedCategories[$parentCategory->id] = true;
                }
            }
        }
        return $usedCategories;
    }

    /**
     * @psalm-return ''|array{id: mixed, title: mixed, url: mixed}
     */
    public function getPartyInfo(): array|string
    {
        if ($party = $this->getPartyElement()) {
            return [
                'id' => $party->id,
                'title' => $party->title,
                'url' => $party->getUrl(),
            ];
        }
        return '';
    }

    /**
     * @return false|string
     */
    public function getCompilationJsonData(): string|false
    {
        $data = [
            'prods' => [],
            'prodsAmount' => count($this->compilationItems),
            'compilations' => [],
            'seriesProds' => [],
        ];
        foreach ($this->compilationItems as $prod) {
            $data['prods'][] = $prod->getElementData('list');
        }
        foreach ($this->seriesProds as $prod) {
            $data['seriesProds'][] = $prod->getElementData('list');
        }
        foreach ($this->compilations as $prod) {
            $data['compilations'][] = $prod->getElementData('list');
        }
        return json_encode($data);
    }

    public function resizeImages(): void
    {
        $pathsManager = $this->getService('PathsManager');
        $configManager = $this->getService('ConfigManager');
        if ($images = $this->getFilesList('connectedFile')) {
            foreach ($images as $image) {
                $filePath = $this->getUploadedFilesPath() . $image->id;
                if (is_file($filePath)) {
                    $info = getimagesize($filePath);
                    $width = $info[0];
                    if ($width > 500) {
                        $imageProcess = new \ImageProcess\ImageProcess($pathsManager->getPath('imagesCache'));
                        $imageProcess->setDefaultCachePermissions($configManager->get('paths.defaultCachePermissions'));
                        $imageProcess->registerImage('canvas', $filePath);
                        $imageProcess->registerFilter(
                            'aspectedResize',
                            'width=' . $width / 2 . ', interpolation=' . IMG_NEAREST_NEIGHBOUR,
                        );
                        $imageProcess->registerExport('canvas', null, $filePath);
                        $imageProcess->executeProcess();
                    }
                }
            }
        }
    }

    public function getMetaTitle()
    {
        $metaData = $this->getMetaData();
        if ($metaData) {
            return $metaData['metaTitle'];
        }
        $title = null;

        /**
         * @var translationsManager $translationsManager
         */
//        $translationsManager = $this->getService('translationsManager');
        if ($categories = $this->getConnectedCategories()) {
            $category = last($categories);
        }
        if ($category) {
            $title = $this->title . ' - ZX Spectrum ' . $category->title;
        }
        if ($this->year) {
            $title .= ' (' . $this->year . ')';
        }
        return $title;

    }

    /**
     * @return (mixed|string)[]
     *
     * @psalm-return array{title: mixed, url: mixed, type: 'article', image: ''|mixed, description: mixed, locale: mixed}
     */
    public function getOpenGraphData()
    {
        $languagesManager = $this->getService('LanguagesManager');
        $data = [
            'title' => $this->getMetaTitle(),
            'url' => $this->getUrl(),
            'type' => 'article',
            'image' => $this->getImage() ? $this->getImage()->getZxImageUrl(true, 1) : '',
            'description' => $this->getMetaDescription(),
            'locale' => $languagesManager->getCurrentLanguage()->iso6391,
        ];
        return $data;
    }

    public function getDescription(): string
    {
        if ($this->description) {
            return $this->description;
        }
        return '';
    }

    public function getGeneratedDescription()
    {
        $metaData = $this->getMetaData();
        return $metaData ? $metaData['generatedDescription'] : '';
    }


    private function getMetaData()
    {
        if (!$this->metaData) {
            $db = $this->getService('db');
            $languagesManager = $this->getService(LanguagesManager::class);
            $this->metaData = $db->table('module_zxprod_meta')
                ->select(['metaTitle', 'h1', 'metaDescription', 'generatedDescription'])
                ->where('id', '=', $this->getId())
                ->where('languageId', '=', $languagesManager->getCurrentLanguageId())
                ->first();
        }
        return $this->metaData;
    }

    public function getMetaDescription()
    {
        $metaData = $this->getMetaData();
        return $metaData ? $metaData['metaDescription'] : '';
    }

    public function getH1()
    {
        $metaData = $this->getMetaData();
        return $metaData ? $metaData['h1'] : '';
    }

    public function checkAndPersistCategories()
    {
        $checkedCategories = [];
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');
        foreach ($this->categories as $categoryId) {
            /**
             * @var zxProdCategoryElement $category
             */
            $category = $structureManager->getElementById($categoryId);
            if (!$category) {
                continue;
            }
            // remove "misc" if there are another categories
            if ($category->getId() === CategoryIds::MISC->value && count($this->categories) > 1) {
                continue;
            }

            $childIds = [];
            $category->getSubCategoriesTreeIds($childIds);
            $isParentId = false;
            foreach ($this->categories as $otherCategoriesId) {
                if ($otherCategoriesId === $categoryId) {
                    continue;
                }
                if (in_array($otherCategoriesId, $childIds)) {
                    $isParentId = true;
                    break;
                }
            }
            if (!$isParentId) {
                $checkedCategories[] = $categoryId;
            }
        }
        $this->categories = $checkedCategories;
        $this->checkLinks('categories', 'zxProdCategory');
    }

    public function getCategoriesCatalogue(): ?zxProdCategoriesCatalogueElement
    {
        $structureManager = $this->getService(structureManager::class);
        $languagesManager = $this->getService(LanguagesManager::class);

        if ($categoriesElements = $structureManager->getElementsByType(
            'zxProdCategoriesCatalogue',
            $languagesManager->getCurrentLanguageId(),
        )) {
            if ($categoriesElement = reset($categoriesElements)) {
                return $categoriesElement;
            }
        }
        return null;
    }

    public function getCatalogueUrl($parameters): string
    {
        $categoriesCatalogue = $this->getCategoriesCatalogue();
        $url = $categoriesCatalogue ? $categoriesCatalogue->getUrl() : "";
        foreach ($parameters as $key => $value) {
            $url .= $key . ':' . $value . '/';
        }
        return $url;
    }
}