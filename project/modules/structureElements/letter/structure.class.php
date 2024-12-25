<?php

/**
 * Class letterElement
 *
 * @property string $title
 */
class letterElement extends structureElement implements ColumnsTypeProvider
{
    use CacheOperatingElement;

    public $dataResourceName = 'module_generic';
    public $allowedTypes = [
        'game',
        'author',
        'authorAlias',
        'group',
        'groupAlias',
    ];
    public $defaultActionName = 'show';
    public $role = 'container';
    protected $authorsList;
    protected $gamesList;
    protected $groupsList;
    protected $lettersSelectorInfo;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getAuthorsList()
    {
        if ($this->authorsList === null) {
            /**
             * @var SectionLogics $sectionLogics
             */
            $sectionLogics = $this->getService('SectionLogics');
            $itemTypes = $sectionLogics->getAuthorLinkTypes();
            $key = implode('-', $itemTypes);
            $cache = $this->getElementsListCache($key, 60 * 60 * 24 * 7);
            if (($this->authorsList = $cache->load()) === null) {
                $this->authorsList = [];
                $linksManager = $this->getService('linksManager');
                $idList = $linksManager->getConnectedIdList($this->id, 'structure', 'parent');
                /**
                 * @var ApiQueriesManager $queriesManager
                 */
                $queriesManager = $this->getService('ApiQueriesManager');


                $parameters = [
                    'authorId' => $idList,
                    'authorOfItemType' => $itemTypes,
                ];
                $query = $queriesManager->getQuery()
                    ->setFiltrationParameters($parameters)
                    ->setExportType('author')
                    ->setResultTypes(['author']);
                if ($result = $query->getQueryResult()) {
                    $this->authorsList = array_merge($this->authorsList, $result['author']);
                }

                $parameters = [
                    'authorAliasId' => $idList,
                    'authorAliasOfItemType' => $itemTypes,
                ];
                $query = $queriesManager->getQuery()
                    ->setFiltrationParameters($parameters)
                    ->setExportType('authorAlias')
                    ->setResultTypes(['authorAlias']);
                if ($result = $query->getQueryResult()) {
                    $this->authorsList = array_merge($this->authorsList, $result['authorAlias']);
                }

                $sort = [];
                foreach ($this->authorsList as $element) {
                    $sort[] = mb_strtolower($element->title);
                }
                array_multisort($sort, SORT_ASC, $this->authorsList);

                $cache->save($this->authorsList);
            }
        }
        return $this->authorsList;
    }

    public function getGamesList()
    {
        if (is_null($this->gamesList)) {
            $structureManager = $this->getService('structureManager');
            $this->gamesList = $structureManager->getElementsChildren($this->id);

            $sort = [];
            foreach ($this->gamesList as $game) {
                $sort[] = strtolower($game->title);
            }
            array_multisort($sort, SORT_ASC, $this->gamesList);
        }
        return $this->gamesList;
    }

    public function getGroupsList()
    {
        if (is_null($this->groupsList)) {
            $structureManager = $this->getService('structureManager');
            $this->groupsList = $structureManager->getElementsChildren($this->id);

            $sort = [];
            foreach ($this->groupsList as $group) {
                $sort[] = strtolower($group->title);
            }
            array_multisort($sort, SORT_ASC, $this->groupsList);
        }
        return $this->groupsList;
    }

    public function getContentList()
    {
        if (is_null($this->contentList)) {
            $structureManager = $this->getService('structureManager');
            if ($contentList = $structureManager->getElementsChildren($this->id)) {
                $sortParameter = [];
                foreach ($contentList as $child) {
                    $sortParameter[] = mb_strtolower($child->title);
                }
                array_multisort($sortParameter, SORT_ASC, $contentList);
            }
            $this->contentList = $contentList;
        }

        return $this->contentList;
    }

    public function updateCataloguesLinks(): void
    {
        $structureManager = $this->getService('structureManager');
        if ($authorsCatalogues = $structureManager->getElementsByType('authorsCatalogue')) {
            $linksManager = $this->getService('linksManager');
            foreach ($authorsCatalogues as $authorsCatalogue) {
                if ($firstParent = $structureManager->getElementsFirstParent($authorsCatalogue->id)) {
                    $linksManager->linkElements($firstParent->id, $this->id, 'authorsCatalogue');
                }
            }
        }
    }

    /**
     * @return void
     */
    public function persistElementData()
    {
        parent::persistElementData();
        $structureManager = $this->getService('structureManager');
        if ($firstParent = $structureManager->getElementsFirstParent($this->id)) {
            if ($firstParent->marker == 'authors') {
                $this->updateCataloguesLinks();
            }
        }
    }

    public function getLettersInfo()
    {
        if ($this->lettersSelectorInfo === null) {
            $this->lettersSelectorInfo = [];
            if (($parentElement = $this->getRequestedParentElement())) {
                if ($letters = $parentElement->getChildrenList(null, [])) {
                    foreach ($letters as $letter) {
                        if ($letter->structureType == 'letter') {
                            $this->lettersSelectorInfo[] = [
                                'url' => $letter->getUrl(),
                                'title' => $letter->title,
                            ];
                        }
                    }
                }
            }
        }
        return $this->lettersSelectorInfo;
    }

    public function getColumnsType()
    {
        return $this->columns;
    }
}