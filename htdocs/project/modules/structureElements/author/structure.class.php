<?php

/**
 * Class authorElement
 *
 * @property string $title
 * @property string $realName
 * @property int $displayInMusic
 * @property int $displayInGraphics
 * @property int $city
 * @property int $country
 * @property int $denyVoting
 * @property int $deny3a
 * @property int $graphicsRating
 * @property int $musicRating
 * @property int $picturesQuantityf
 * @property int $tunesQuantity
 * @property int $joinAsAlias
 * @property int $joinAndDelete
 * @property int $artCityId
 * @property int $zxTunesId
 * @property int $image
 * @property string $originalName
 * @property string $wikiLink
 * @property string $email
 * @property string $site
 */
class authorElement extends structureElement implements CommentsHolderInterface, AliasesHolder, JsonDataProvider
{
    use JsonDataProviderElement;
    use CacheOperatingElement;
    use LocationProviderTrait;
    use ChartDataProviderTrait;
    use UserElementProviderTrait;
    use LettersElementsListProviderTrait;
    use Author;
    use AuthorshipProviderTrait;
    use AliasElementsProvider;
    use CommentsTrait;
    use ImportedItemTrait;
    use MusicSettingsProvider;
    use PaletteTypesProvider;
    use PublisherProdsProvider;

    public $dataResourceName = 'module_author';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $userId;
    protected $viewName = 'details';
    protected $prods;
    protected $releases;

    protected $groupsList;

    protected function setModuleStructure(&$moduleStructure)
    {
        //data
        $moduleStructure['title'] = 'text';
        $moduleStructure['realName'] = 'text';
        $moduleStructure['country'] = 'text';
        $moduleStructure['city'] = 'text';
        $moduleStructure['artCityId'] = 'text';
        $moduleStructure['zxTunesId'] = 'text';
        $moduleStructure['wikiLink'] = 'text';
        $moduleStructure['email'] = 'text';
        $moduleStructure['site'] = 'url';
        $moduleStructure['image'] = 'image';
        $moduleStructure['originalName'] = 'text';
        //settings
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';
        $moduleStructure['displayInGraphics'] = 'checkbox';
        $moduleStructure['displayInMusic'] = 'checkbox';
        $moduleStructure['deny3a'] = 'checkbox';

        $moduleStructure['chipType'] = 'text';
        $moduleStructure['channelsType'] = 'text';
        $moduleStructure['frequency'] = 'text';
        $moduleStructure['intFrequency'] = 'text';
        $moduleStructure['palette'] = 'text';

        //stats
        $moduleStructure['graphicsRating'] = 'floatNumber';
        $moduleStructure['musicRating'] = 'floatNumber';
        $moduleStructure['picturesQuantity'] = 'text';
        $moduleStructure['tunesQuantity'] = 'text';

        $moduleStructure['joinAsAlias'] = 'text';
        $moduleStructure['joinAndDelete'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'realName';
    }

    protected function getCityId()
    {
        return $this->city;
    }

    protected function getCountryId()
    {
        return $this->country;
    }

    public function isVotingDenied()
    {
        return $this->denyVoting;
    }

    public function is3aDenied()
    {
        return $this->deny3a;
    }

    public function getWorksList($types)
    {
        $result = [];
        foreach ($types as $type) {
            if (!isset($this->worksList[$type])) {
                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                $this->worksList[$type] = $structureManager->getElementsChildren($this->id, null, $type);
                if ($aliasElements = $this->getAliasElements()) {
                    /**
                     * @var authorAliasElement $aliasElement
                     */
                    foreach ($aliasElements as $aliasElement) {
                        if ($aliasWorks = $aliasElement->getWorksList([$type])) {
                            $this->worksList[$type] = array_merge($this->worksList[$type], $aliasWorks);
                        }
                    }
                }
            }
            $result = array_merge($result, $this->worksList[$type]);
        }
        return $result;
    }

    public function recalculateAuthorData()
    {
        $average = $this->getService('ConfigManager')->get('zx.averageVote');
        $votes = 0;
        if ($pictures = $this->getWorksList(['authorPicture'])) {
            foreach ($pictures as $picture) {
                if ($picture->votes >= $average) {
                    $votes += (pow(1 + $picture->votes - $average, 3) - 1);
                }
            }
        }
        $this->graphicsRating = $votes;
        $this->picturesQuantity = count($pictures);

        $votes = 0;
        if ($musicList = $this->getWorksList(['authorMusic'])) {
            foreach ($musicList as $music) {
                if ($music->votes >= $average) {
                    $votes += (pow(1 + $music->votes - $average, 3) - 1);
                }
            }
        }
        $this->musicRating = $votes;
        $this->tunesQuantity = count($musicList);

        if ($this->tunesQuantity > 0) {
            $this->displayInMusic = 1;
        }
        if ($this->picturesQuantity > 0) {
            $this->displayInGraphics = 1;
        }
        $this->checkCountry();

        $this->persistElementData();
    }

    public function checkCountry()
    {
        if ($country = $this->getCountryElement()) {
            if ($city = $this->getCityElement()) {
                $parentCountry = $city->getFirstParentElement();
                if ($parentCountry !== $country) {
                    $this->countryElement = null;
                    $this->country = $parentCountry->id;
                    return false;
                }
            }
        }
        return true;
    }

    public function recalculatePicturesData()
    {
        if ($pictures = $this->getWorksList(['authorPicture'])) {
            foreach ($pictures as $picture) {
                $picture->recalculate();
            }
        }
    }

    public function recalculateMusicData()
    {
        if ($musicList = $this->getWorksList(['authorMusic'])) {
            foreach ($musicList as $music) {
                $music->recalculate();
            }
        }
    }

    public function reconvertMusic()
    {
        if ($musicList = $this->getWorksList(['authorMusic'])) {
            foreach ($musicList as $music) {
                $music->checkIfReconversionNeeded();
            }
        }
    }

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $useBlackList = false)
    {
        if ($result = $this->getWorksList(['authorMusic', 'authorPicture'])) {
            $sort = [];
            foreach ($result as $element) {
                $sort[] = mb_strtolower($element->structureType . $element->title);
            }
            array_multisort($sort, SORT_ASC, $result);
        }
        return $result;
    }

    public function getChartDataIds($type = null)
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService('linksManager');
        return $linksManager->getConnectedIdList($this->id, $type, 'parent');
    }

    public function getChartDataEventTypes($type = null)
    {
        if ($type == 'authorMusic') {
            return ['play'];
        } else {
            return ['view'];
        }
    }

    public function getSaveUrl($type)
    {
        /**
         * @var LanguagesManager $languagesManager
         */
        $languagesManager = $this->getService('LanguagesManager');
        $controller = controller::getInstance();
        $url = $controller->baseURL . 'zipItems/';
        $url .= 'export:' . $type . '/';
        $url .= 'language:' . $languagesManager->getCurrentLanguageCode() . '/';
        $url .= 'filter:authorId=' . $this->id . '/';
        $url .= 'structure:authors/';

        return $url;
    }

    public function getUserId()
    {
        if ($this->userId === null) {
            /**
             * @var $db \Illuminate\Database\MySqlConnection
             */
            $db = $this->getService('db');
            $this->userId = $db->table('module_user')
                ->where('authorId', '=', $this->id)
                ->limit(1)
                ->value('id');
        }
        return $this->userId;
    }

    public function getProds()
    {
        if ($this->prods === null) {
            $this->prods = [];
            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');
            if ($authorShip = $authorsManager->getAuthorshipInfo($this->id, 'prod')) {
                foreach ($authorShip as $item) {
                    $this->prods[] = $item['prodElement'];
                }
            }
            if ($aliasElements = $this->getAliasElements()) {
                foreach ($aliasElements as $aliasElement) {
                    if ($prods = $aliasElement->getProds()) {
                        foreach ($prods as $prodElement) {
                            $this->prods[] = $prodElement;
                        }
                    }
                }
            }
            $sort = [];
            foreach ($this->prods as $prod) {
                $sort[] = $prod->getTitle();
            }
            array_multisort($sort, SORT_ASC, $this->prods);
        }

        return $this->prods;
    }

    public function getReleases()
    {
        if ($this->releases === null) {
            $this->releases = [];
            /**
             * @var linksManager $linksManager
             */

            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');
            if ($authorShip = $authorsManager->getAuthorshipInfo($this->id, 'release')) {
                foreach ($authorShip as $item) {
                    $this->releases[] = $item['releaseElement'];
                }
            }
            if ($aliasElements = $this->getAliasElements()) {
                foreach ($aliasElements as $aliasElement) {
                    if ($releases = $aliasElement->getReleases()) {
                        foreach ($releases as $releaseElement) {
                            $this->releases[] = $releaseElement;
                        }
                    }
                }
            }

            $sort = [];
            foreach ($this->releases as $release) {
                $sort[] = $release->getTitle();
            }
            array_multisort($sort, SORT_ASC, $this->releases);
        }


        return $this->releases;
    }

    public function getTitle()
    {
        if ($this->title) {
            return $this->title;
        } elseif ($this->realName) {
            return $this->realName;
        }
        return parent::getHumanReadableName();
    }

    public function getGroupsList()
    {
        if ($this->groupsList === null) {
            $cache = $this->getElementsListCache('g', 60 * 60 * 24);
            if (($this->groupsList = $cache->load()) === false) {
                $this->groupsList = [];
                if ($authorshipInfo = $this->getAuthorshipInfo('group')) {
                    foreach ($authorshipInfo as $item) {
                        $this->groupsList[] = $item['groupElement'];
                    }
                }
                if ($aliasElements = $this->getAliasElements()) {
                    foreach ($aliasElements as $aliasElement) {
                        if ($authorshipInfo = $aliasElement->getAuthorshipInfo('group')) {
                            foreach ($authorshipInfo as $item) {
                                if (!in_array($item['groupElement'], $this->groupsList)) {
                                    $this->groupsList[] = $item['groupElement'];
                                }
                            }
                        }
                    }
                }
                $cache->save($this->groupsList);
            }
        }
        return $this->groupsList;
    }

    public function getSearchTitle()
    {
        $searchTitle = $this->title;
        $additional = '';
        if ($this->realName && $this->realName !== $this->title) {
            $additional .= $this->realName;
        }
        if ($country = $this->getCountryTitle()) {
            if ($additional) {
                $additional .= ', ';
            }
            $additional .= $country;
        }
        if ($additional) {
            if ($searchTitle) {
                $searchTitle .= ' (' . $additional . ')';
            } else {
                $searchTitle = $additional;
            }
        }
        return $searchTitle;
    }

    public function getChipType()
    {
        $chipType = $this->chipType;
        if (!$chipType) {
            $chipType = 'ym';
        }
        return $chipType;
    }

    public function getFrequency()
    {
        $frequency = $this->frequency;
        if (!$frequency) {
            $frequency = 1770000;
        }
        return $frequency;
    }

    public function getIntFrequency()
    {
        $intFrequency = $this->intFrequency;
        if (!$intFrequency) {
            $intFrequency = 50;
        }
        return $intFrequency;
    }

    public function getChannelsType()
    {
        $channelsType = $this->channelsType;
        if (!$channelsType) {
            $channelsType = 'ACB';
        }
        return $channelsType;
    }

    public function getPalette()
    {
        $channelsType = $this->palette;
        if (!$channelsType) {
            $channelsType = 'srgb';
        }
        return $channelsType;
    }
}