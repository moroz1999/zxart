<?php

/**
 * Class groupAliasElement
 *
 * @property string title
 * @property int startDate
 * @property int endDate
 * @property int groupId
 */
class groupAliasElement extends structureElement implements CommentsHolderInterface, JsonDataProvider
{
    use JsonDataProviderElement;
    use AuthorshipPersister;
    use ImportedItemTrait;
    use Group;
    use PublisherProdsProvider;
    use ReleasesProvider;
    use CommentsTrait;

    public $dataResourceName = 'module_groupalias';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $groupProds;
    /**
     * @var groupElement
     */
    protected $groupElement;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['startDate'] = 'date';
        $moduleStructure['endDate'] = 'date';
        $moduleStructure['groupId'] = 'text';

        $moduleStructure['addAuthor'] = 'text';
        $moduleStructure['addAuthorStartDate'] = 'array';
        $moduleStructure['addAuthorEndDate'] = 'array';
        $moduleStructure['addAuthorRole'] = 'array';

        $moduleStructure['joinAndDelete'] = 'text';
        $moduleStructure['publishedReleases'] = [
            'ConnectedElements',
            [
                'linkType' => 'zxReleasePublishers',
                'role' => 'parent',
            ],
        ];
    }

    public function getGroupElement()
    {
        if ($this->groupElement === null) {
            if ($groupId = $this->getGroupId()) {
                $structureManager = $this->getService('structureManager');
                if ($groupElement = $structureManager->getElementById($groupId)) {
                    if ($groupElement->structureType == 'group') {
                        $this->groupElement = $groupElement;
                    }
                }
            }
        }
        return $this->groupElement;
    }

    public function getGroupId()
    {
        if ($this->groupId) {
            return $this->groupId;
        }
        return false;
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
        }

        return $this->groupProds;
    }

    public function getAliasElements()
    {
        return false;
    }

    public function getSearchTitle()
    {
        $searchTitle = $this->title;
        if ($groupElement = $this->getGroupElement()) {
            $searchTitle .= ' (' . $groupElement->getTitle() . ')';
        }
        return $searchTitle;
    }
}