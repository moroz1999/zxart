<?php

/**
 * Class groupElement
 *
 * @property string $title
 * @property string $abbreviation
 * @property string $website
 * @property string $wikiLink
 * @property string $slogan
 * @property int $city
 * @property int $country
 * @property int[] $subGroupsSelector
 * @property int $joinAsAlias
 * @property int $joinAndDelete
 * @property zxReleaseElement[] $publishedReleases
 */
class groupElement extends structureElement implements AliasesHolder, CommentsHolderInterface
{
    use AuthorshipPersister;
    use LocationProviderTrait;
    use LettersElementsListProviderTrait;
    use AliasElementsProvider;
    use ImportedItemTrait;
    use Group;
    use PublisherProdsProvider;
    use CommentsTrait;

    const SUBGROUP_LINK_TYPE = 'groupSub';
    public $dataResourceName = 'module_group';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected $subGroups;
    protected $subGroupIds;
    protected $groupProds;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['country'] = 'text';
        $moduleStructure['city'] = 'text';
        $moduleStructure['image'] = 'image';
        $moduleStructure['originalName'] = 'text';
        $moduleStructure['wikiLink'] = 'text';
        $moduleStructure['website'] = 'url';
        $moduleStructure['abbreviation'] = 'text';
        $moduleStructure['type'] = 'serializedIndex';
        $moduleStructure['startDate'] = 'date';
        $moduleStructure['endDate'] = 'date';
        $moduleStructure['slogan'] = 'text';

        $moduleStructure['subGroupsSelector'] = 'numbersArray';

        $moduleStructure['addAuthor'] = 'text';
        $moduleStructure['addAuthorStartDate'] = 'array';
        $moduleStructure['addAuthorEndDate'] = 'array';
        $moduleStructure['addAuthorRole'] = 'array';

        $moduleStructure['joinAsAlias'] = 'text';
        $moduleStructure['joinAndDelete'] = 'text';
        $moduleStructure['publishedReleases'] = [
            'ConnectedElements',
            [
                'linkType' => 'zxReleasePublishers',
                'role' => 'parent',
            ],
        ];
    }

    protected function getCityId()
    {
        return $this->city;
    }

    protected function getCountryId()
    {
        return $this->country;
    }

    public function getChartDataIds($type = null)
    {
        return $this->getService('linksManager')->getConnectedIdList($this->id, $type, 'parent');
    }

    public function getChartDataEventTypes($type = null)
    {
        if ($type == 'groupMusic') {
            return ['play'];
        } else {
            return ['view'];
        }
    }

    public function getSaveUrl($type)
    {
        $controller = controller::getInstance();
        $url = $controller->baseURL . 'zipItems/';
        $url .= 'export:' . $type . '/';
        $url .= 'language:' . $this->getService('LanguagesManager')->getCurrentLanguageCode() . '/';
        $url .= 'filter:groupId=' . $this->id . '/';
        $url .= 'structure:groups/';

        return $url;
    }

    public function checkParentLetter()
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService('linksManager');
        $letterId = $this->getLetterId($this->title);

        if ($links = $linksManager->getElementsLinks($this->id)) {
            $link = reset($links);
            if ($link->parentStructureId != $letterId) {
                $linksManager->unLinkElements($link->parentStructureId, $this->id);
                $linksManager->linkElements($letterId, $this->id);
                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                $structureManager->regenerateStructureInfo($this);
            }
        }
    }

    protected function getLettersListMarker($type)
    {
        if ($type == 'admin') {
            return 'groups';
        } else {
            return 'groupsmenu';
        }
    }

    public function getSubGroups()
    {
        if ($this->subGroups === null) {
            $this->subGroups = [];
            if ($categoriesIds = $this->getSubGroupIds()) {
                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                foreach ($categoriesIds as $categoryId) {
                    if ($categoryId && $productElement = $structureManager->getElementById($categoryId)) {
                        $this->subGroups[] = $productElement;
                    }
                }
            }
        }
        return $this->subGroups;
    }

    public function getSubGroupIds()
    {
        if ($this->subGroupIds === null) {
            /**
             * @var linksManager $linksManager
             */
            $linksManager = $this->getService('linksManager');
            $this->subGroupIds = $linksManager->getConnectedIdList($this->id, self::SUBGROUP_LINK_TYPE, "parent");
        }
        return $this->subGroupIds;
    }

    public function persistSubGroupConnections()
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService('linksManager');
        if ($subGroupIds = $this->getSubGroupIds()) {
            foreach ($subGroupIds as $subGroupId) {
                if (!in_array($subGroupId, $this->subGroupsSelector)) {
                    $linksManager->unLinkElements($subGroupId, $this->id, self::SUBGROUP_LINK_TYPE);
                }
            }
        }
        foreach ($this->subGroupsSelector as $id) {
            $linksManager->linkElements($this->id, $id, self::SUBGROUP_LINK_TYPE);
        }
    }

    public function getGroupProds()
    {
        if ($this->groupProds === null) {
            $this->groupProds = [];
            /**
             * @var linksManager $linksManager
             */
            $linksManager = $this->getService('linksManager');
            if ($prodIds = $linksManager->getConnectedIdList($this->id, 'zxProdGroups', 'parent')) {
                $structureManager = $this->getService('structureManager');
                foreach ($prodIds as $prodId) {
                    if ($prodElement = $structureManager->getElementById($prodId)) {
                        $this->groupProds[] = $prodElement;
                    }
                }
            }
            if ($aliasElements = $this->getAliasElements()) {
                foreach ($aliasElements as $aliasElement) {
                    if ($prods = $aliasElement->getGroupProds()) {
                        foreach ($prods as $prodElement) {
                            $this->groupProds[] = $prodElement;
                        }
                    }
                }
            }
            $sort = [];
            foreach ($this->groupProds as $prod) {
                $sort[] = trim($prod->getHumanReadableName());
            }
            array_multisort($sort, SORT_ASC, $this->groupProds);
        }

        return $this->groupProds;
    }

    public function getSearchTitle()
    {
        $searchTitle = $this->getTitle();
        $additional = '';
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
}