<?php

use ZxArt\Elements\PressMentionsProvider;
use ZxArt\Groups\GroupTypeProvider;
use ZxArt\LinkTypes;

/**
 * Class groupElement
 *
 * @property string $title
 * @property string $abbreviation
 * @property string $website
 * @property string $wikiLink
 * @property string $slogan
 * @property string $type
 * @property int $city
 * @property int $country
 * @property int[] $subGroupsSelector
 * @property int $joinAsAlias
 * @property int $joinAndDelete
 * @property pressArticleElement[] $mentions
 * @property zxReleaseElement[] $publishedReleases
 * @property groupElement[] $parentGroups
 */
class groupElement extends structureElement implements
    AliasesHolder,
    CommentsHolderInterface,
    JsonDataProvider,
    Recalculable,
    LocationProvider,
    PressMentionsProvider
{
    use JsonDataProviderElement;
    use AuthorshipPersister;
    use LocationProviderTrait;
    use LettersElementsListProviderTrait;
    use AliasElementsProvider;
    use ImportedItemTrait;
    use Group;
    use PublisherProdsProvider;
    use ReleasesProvider;
    use CommentsTrait;
    use GroupTypeProvider;

    public $dataResourceName = 'module_group';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected $subGroups;
    protected $subGroupIds;
    protected $groupProds;

    /**
     * @return void
     */
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
        $moduleStructure['startDate'] = 'date';
        $moduleStructure['endDate'] = 'date';
        $moduleStructure['slogan'] = 'text';
        $moduleStructure['type'] = 'text';

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
        $moduleStructure['parentGroups'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::GROUP_SUBGROUP->value,
                'role' => 'child',
            ],
        ];
        $moduleStructure['mentions'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_GROUPS->value,
                'role' => 'parent',
            ],
        ];
    }

    /**
     * @return int
     */
    protected function getCityId()
    {
        return $this->city;
    }

    /**
     * @return int
     */
    protected function getCountryId()
    {
        return $this->country;
    }

    public function getChartDataIds($type = null)
    {
        return $this->getService('linksManager')->getConnectedIdList($this->getId(), $type, 'parent');
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'play'|'view'}
     */
    public function getChartDataEventTypes($type = null): array
    {
        if ($type == 'groupMusic') {
            return ['play'];
        } else {
            return ['view'];
        }
    }

    public function getSaveUrl($type): string
    {
        $controller = controller::getInstance();
        $url = $controller->baseURL . 'zipItems/';
        $url .= 'export:' . $type . '/';
        $url .= 'language:' . $this->getService('LanguagesManager')->getCurrentLanguageCode() . '/';
        $url .= 'filter:groupId=' . $this->getId() . '/';
        $url .= 'structure:groups/';

        return $url;
    }

    public function checkParentLetter(): void
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService('linksManager');
        $letterId = $this->getLetterId($this->title);

        if ($links = $linksManager->getElementsLinks($this->getId())) {
            $link = reset($links);
            if ($link->parentStructureId != $letterId) {
                $linksManager->unLinkElements($link->parentStructureId, $this->getId());
                $linksManager->linkElements($letterId, $this->getId());
                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                $structureManager->regenerateStructureInfo($this);
            }
        }
    }

    /**
     * @psalm-param 'admin'|'public' $type
     *
     * @return string
     *
     * @psalm-return 'groups'|'groupsmenu'
     */
    protected function getLettersListMarker(string $type)
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
            $this->subGroupIds = $linksManager->getConnectedIdList($this->getId(), LinkTypes::GROUP_SUBGROUP->value, "parent");
        }
        return $this->subGroupIds;
    }

    public function persistSubGroupConnections(): void
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService('linksManager');
        if ($subGroupIds = $this->getSubGroupIds()) {
            foreach ($subGroupIds as $subGroupId) {
                if (!in_array($subGroupId, $this->subGroupsSelector)) {
                    $linksManager->unLinkElements($subGroupId, $this->getId(), LinkTypes::GROUP_SUBGROUP->value);
                }
            }
        }
        foreach ($this->subGroupsSelector as $id) {
            $linksManager->linkElements($this->getId(), $id, LinkTypes::GROUP_SUBGROUP->value);
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
            if ($prodIds = $linksManager->getConnectedIdList($this->getId(), 'zxProdGroups', 'parent')) {
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
                $sort[] = trim($prod->getTitle());
            }
            array_multisort($sort, SORT_ASC, $this->groupProds);
        }

        return $this->groupProds;
    }

    public function getSearchTitle(): string
    {
        $searchTitle = $this->getTitle();
        $additional = '';
        if ($country = $this->getCountryTitle()) {
            if ($additional) {
                $additional .= ', ';
            }
            $additional .= $country;
        }
        if ($city = $this->getCityTitle()) {
            if ($additional) {
                $additional .= ', ';
            }
            $additional .= $city;
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

    /**
     * @return void
     */
    public function recalculate()
    {
        $this->checkCountry();
        $this->persistElementData();
    }

    public function getPressMentions(): array
    {
        $mentions = [$this->mentions];
        if ($aliasElements = $this->getAliasElements()) {
            foreach ($aliasElements as $aliasElement) {
                $mentions[] = $aliasElement->getPressMentions();
            }
        }
        $allArticles = array_merge(...$mentions);
        usort($allArticles, static function ($a, $b) {
            $press1 = $a->getParent();
            $press2 = $b->getParent();
            if ($a->year === $b->year) {
                if ($press1->id === $press2->id){
                    return strcmp($a->title, $b->title);
                }
                return strcmp($press1->title, $press2->title);
            }
            return $a->year - $b->year;
        });
        return $allArticles;
    }
}