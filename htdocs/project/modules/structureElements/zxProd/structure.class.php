<?php

/**
 * Class zxProdElement
 *
 * @property string $title
 * @property string $year
 * @property string $youtubeId
 * @property string $description
 * @property string $legalStatus
 * @property string $compo
 * @property string[] $language
 * @property string[] $externalLink
 * @property int $party
 * @property int[] $categories
 * @property groupElement[] $publishers
 * @property groupElement[] $groups
 * @property zxProdElement[] $compilationProds
 * @property float $votes
 * @property int $partyplace
 * @property int $denyVoting
 * @property int $denyComments
 * @property int $dateAdded
 * @property int $userId
 * @property array[] $splitData
 */
class zxProdElement extends ZxArtItem implements StructureElementUploadedFilesPathInterface, CommentsHolderInterface,
                                                 JsonDataProvider
{
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

    public $dataResourceName = 'module_zxprod';
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
    protected $firstImage;
    protected $images = [];
    protected $bestPictures;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';

        $moduleStructure['party'] = 'text';
        $moduleStructure['partyplace'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['year'] = 'text';
        $moduleStructure['youtubeId'] = 'text';
        $moduleStructure['description'] = 'pre';
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
                'tableName' => $this->dataResourceName . '_language',
            ],
        ];
        $moduleStructure['compilationProds'] = [
            'ConnectedElements',
            [
                'linkType' => 'compilation',
                'role' => 'parent',
            ],
        ];
        $moduleStructure['joinAndDelete'] = 'text';
        $moduleStructure['splitData'] = 'array';
    }

    public function getFileSelectorPropertyNames()
    {
        return ['connectedFile', 'mapFilesSelector'];
    }

    public function getPartyId()
    {
        return $this->party;
    }

    public function getFileExtension($extensionType)
    {
        $extension = '';
        return $extension;
    }

    protected function fileExists($extensionType)
    {
        return false;
    }

    public function getChartDataEventTypes($type = null)
    {
        return ['view'];
    }

    /**
     * @return zxReleaseElement[]
     */
    public function getReleasesList()
    {
        if ($this->releasesList === null) {
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            $this->releasesList = $structureManager->getElementsChildren($this->id);
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
        return $linksManager->getConnectedIdList($this->id, 'structure', 'parent');
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
        foreach ($this->compilationProds as $prod) {
            if ($image = $prod->getImage($number)) {
                return $image;
            }
        }

        return false;
    }

    //used in API
    public function getImagesUrls()
    {
        $urls = [];
        foreach ($this->getFilesList('connectedFile') as $fileElement) {
            $urls[] = $fileElement->getImageUrl('prodImage');
        }
        return $urls;
    }

    public function getImageUrl($number = 0)
    {
        if ($image = $this->getImage($number)) {
            return $image->getImageUrl('prodImage');
        } elseif ($number == 0) {
            $controller = $this->getService('controller');
            if ($this->legalStatus == 'unreleased') {
                return $controller->baseURL . 'project/images/public/zxprod_unreleased.png';
            } elseif ($this->legalStatus == 'mia') {
                return $controller->baseURL . 'project/images/public/zxprod_mia.png';
            }
            return $controller->baseURL . 'project/images/public/zxprod_default.png';
        }
        return false;
    }

    protected function getDeletionLinkTypes()
    {
        return ['structure', $this->getConnectedFileType()];
    }

    public function persistElementData()
    {
        if (!$this->hasActualStructureInfo()) {
            $this->dateAdded = time();
        }
        parent::persistElementData();
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
                'zxProdId' => [$this->id],
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

    public function getLegalStatus()
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
                ->getConnectedIdList($this->id, 'zxProdCategory', 'child');
            if (!$this->connectedCategoriesIds) {
                if ($element = $this->getFirstParentElement()) {
                    if ($element->structureType == 'zxProdCategory') {
                        $this->connectedCategoriesIds = [$element->id];
                    }
                }
            }
        }
        return $this->connectedCategoriesIds;
    }

    public function getSupportedLanguageCodes()
    {
        return $this->language;
    }

    public function getFileUploadSuccessUrl()
    {
        return false;
    }

    public function isPrivilegesSettingRequired()
    {
        return true;
    }

    public function getImagesList()
    {
        $result = [];
        $result = array_merge($result, $this->getFilesList('connectedFile'));


        foreach ($this->getReleasesList() as $releaseElement) {
            $result = array_merge($result, $releaseElement->getImagesList());
        }
        $result = array_merge($result, $this->getFilesList('mapFilesSelector'));

        return $result;
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

            if ($this->is3aDenied()) {
                $types = ['zxdb', 'vt', 'dzoo', 'pouet', 'zxd'];
            } else {
                $types = ['3a', 'zxdb', 'vt', 'dzoo', 'pouet', 'zxd'];
            }

            $query = $db->table('import_origin')
                ->select('importId', 'importOrigin')
                ->where('elementId', '=', $this->id)
                ->whereIn('importOrigin', $types);
            if ($rows = $query->get()) {
                foreach ($rows as $row) {
                    if ($row['importOrigin'] == 'zxdb') {
                        if ($this->structureType == 'zxProd') {
                            $this->linksInfo[] = [
                                'type' => 'sc',
                                'image' => 'icon_sc.png',
                                'name' => $translationsManager->getTranslationByName('links.link_sc'),
                                'url' => 'http://spectrumcomputing.co.uk/index.php?cat=96&id=' . $row['importId'],
                                'id' => $row['importId'],
                            ];
                            if ($row['importId'] < 28188) {
                                $row['importId'] = sprintf("%07d", $row['importId']);
                                $this->linksInfo[] = [
                                    'type' => 'wos',
                                    'image' => 'icon_wos.png',
                                    'name' => $translationsManager->getTranslationByName('links.link_wos'),
                                    'url' => 'http://www.worldofspectrum.org/infoseekid.cgi?id=' . $row['importId'] . '&loadpics=1',
                                    'id' => $row['importId'],
                                ];
                            }
                        }
                    } elseif ($row['importOrigin'] == '3a') {
                        $this->linksInfo[] = [
                            'type' => '3a',
                            'image' => 'icon_3a.png',
                            'name' => $translationsManager->getTranslationByName('links.link_3a'),
                            'url' => 'https://zxaaa.net/view_demo.php?id=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] == 'pouet') {
                        $this->linksInfo[] = [
                            'type' => 'pouet',
                            'image' => 'icon_pouet.png',
                            'name' => $translationsManager->getTranslationByName('links.link_pouet'),
                            'url' => 'http://www.pouet.net/prod.php?which=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] == 'dzoo') {
                        $this->linksInfo[] = [
                            'type' => 'dzoo',
                            'image' => 'icon_dzoo.png',
                            'name' => $translationsManager->getTranslationByName('links.link_dzoo'),
                            'url' => 'https://demozoo.org/productions/' . $row['importId'] . '/',
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] == 'zxd') {
                        $this->linksInfo[] = [
                            'type' => 'zxd',
                            'image' => 'icon_zxd.png',
                            'name' => $translationsManager->getTranslationByName('links.link_zxd'),
                            'url' => 'http://zxdemo.org/productions/' . $row['importId'] . '/',
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

    public function getSearchTitle()
    {
        $searchTitle = $this->title;
        if ($this->year) {
            $searchTitle .= ' (' . $this->year . ')';
        }
        return $searchTitle;
    }

    public function is3aDenied()
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

    public function getSplitData()
    {
        $data = [];
        $properties = ['title', 'year'];
        foreach ($properties as $property) {
            if ($this->$property) {
                $data['properties'][$property] = $this->$property;
            }
        }
        foreach ($this->getAuthorsInfo('prod') as $authorInfo) {
            if ($authorElement = $authorInfo['authorElement']) {
                $data['authors'][$authorInfo['id']] = $authorElement->id . ' <a target="_blank" href="' . $authorElement->getUrl(
                    ) . '">' . $authorElement->getSearchTitle() . '</a>';
            }
        }
        foreach ($this->publishers as $publisher) {
            $data['publishers'][$publisher->id] = $publisher->id . ' <a target="_blank" href="' . $publisher->getUrl(
                ) . '">' . $publisher->getSearchTitle() . '</a>';
        }
        foreach ($this->groups as $group) {
            $data['groups'][$group->id] = $group->id . ' <a target="_blank" href="' . $group->getUrl(
                ) . '">' . $group->getSearchTitle() . '</a>';
        }
        foreach ($this->getReleasesList() as $releaseElement) {
            $data['releases'][$releaseElement->id] = $releaseElement->id . ' <a target="_blank" href="' . $releaseElement->getUrl(
                ) . '">' . $releaseElement->getSearchTitle() . '</a>';
        }
        foreach ($this->getFilesList('connectedFile') as $fileElement) {
            $data['screenshots'][$fileElement->id] = $fileElement->id . ' <img style="height: 5rem" src="' . $fileElement->getImageUrl(
                    'prodImage'
                ) . '" />';
        }

        foreach ($this->getLinksInfo() as $linkInfo) {
            $data['links'][$linkInfo['type'] . ';' . $linkInfo['id']] = $linkInfo['type'] . ' <a target="_blank" href="' . $linkInfo['url'] . '">' . $linkInfo['id'] . '</a>';;
        }

        return $data;
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

    public function getTemplatedMetaTitle()
    {
        if ($categoryMetaTitleTemplate = $this->getFirstDataFromParents('metaTitleTemplate')) {
            return $this->populateSeoTemplate($categoryMetaTitleTemplate);
        }
        return '';
    }

    public function getTemplatedH1()
    {
        if ($categoryH1Template = $this->getFirstDataFromParents('metaH1Template')) {
            return $this->populateSeoTemplate($categoryH1Template);
        }
        return '';
    }

    private function getFirstDataFromParents($parameterName)
    {
        $categories = [];

        foreach ($this->getConnectedCategories() as $category) {
            $element = $category;
            $level = 0;

            if (!empty($category->{$parameterName})) {
                $parameter = $category->{$parameterName};
                $parameterLevel = 0;
            } else {
                $parameter = '';
                $parameterLevel = false;
            }

            while ($element) {
                $element = $element->getCurrentParentElement();
                if ($element && $element->structureType == 'zxProdCategory') {
                    $level++;

                    if (!$parameter && $element->{$parameterName}) {
                        $parameter = $element->{$parameterName};
                        $parameterLevel = $level;
                    }
                }
            }

            if ($parameter) {
                $categories[$level][$parameterLevel] = $parameter;
            }
        }

        krsort($categories);
        $parameters = array_shift($categories);
        if ($parameters) {
            ksort($parameters);
            $parameter = array_shift($parameters);
        } else {
            $parameter = '';
        }

        return $parameter;
    }

    protected function populateSeoTemplate($template)
    {
        $translationsManager = $this->getService('translationsManager');

        preg_match_all("|{(.*)}|sUi", $template, $results);
        $search = [];
        $replace = [];
        foreach ($results[1] as $result) {
            $search[] = '{' . $result . '}';
            switch ($result) {
                case 'title':
                {
                    $replace[] = $this->title;
                    break;
                }
                case 'category':
                {
                    if ($category = $this->getRequestedParentCategory()) {
                        $replace[] = $category->title;
                    }
                    break;
                }
                case 'topCategory':
                {
                    if ($category = $this->getRequestedTopCategory()) {
                        $replace[] = $category->title;
                    }
                    break;
                }
                case 'brand':
                {
                    $brand = $this->getBrandElement();
                    $replace[] = $brand->title;
                    break;
                }
                case 'price':
                {
                    $replace[] = $this->getPrice();
                    break;
                }
                case 'availability':
                {
                    $replace[] = $translationsManager->getTranslationByName('product.' . $this->availability);
                    break;
                }
                case 'deliveryStatus':
                {
                    $replace[] = $this->getDeliveryStatus();
                    break;
                }
                case (stripos($result, 'parameterValue:') !== false):
                {
                    $id = (int)substr($result, 15);
                    if ($value = $this->getParameterValueById($id)) {
                        $replace[] = mb_strtolower($value);
                    } else {
                        $replace[] = '';
                    }
                    break;
                }
                default:
                    $replace[] = '';
            }
        }

        return str_replace($search, $replace, $template);
    }

    public function getTextContent()
    {
        if ($this->textContent === null) {
            $this->textContent = '';
            if ($this->final) {
                if ($categoryMetaDescriptionTemplate = $this->getFirstDataFromParents('metaDescriptionTemplate')) {
                    $this->textContent = $this->populateSeoTemplate($categoryMetaDescriptionTemplate);
                }
            }
        }
        return $this->textContent;
    }


}