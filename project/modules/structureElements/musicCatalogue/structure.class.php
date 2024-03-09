<?php

class musicCatalogueElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $allowedTypes = ['zxMusic'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected $musicPageList;

    public $pager;
    public $picturesList;

    protected $gamesList;
    protected $partiesList;
    protected $authorsList;
    protected $authorsIDList;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';

        $moduleStructure['description'] = 'textarea';
        $moduleStructure['party'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['game'] = 'text';
        $moduleStructure['author'] = 'numbersArray';
        $moduleStructure['type'] = 'text';
        $moduleStructure['year'] = 'text';

        $moduleStructure['music'] = 'files';

        $moduleStructure['tagsText'] = 'text';
        $moduleStructure['chipType'] = 'text';
        $moduleStructure['channelsType'] = 'text';
        $moduleStructure['frequency'] = 'text';
        $moduleStructure['intFrequency'] = 'text';
        $moduleStructure['palette'] = 'text';
        $moduleStructure['formatGroup'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
        $multiLanguageFields[] = 'title';
    }

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $useBlackList = false)
    {
        if ($roles != 'container') {
            if (is_null($this->musicPageList)) {
                $structureManager = $this->getService('structureManager');
                $this->musicPageList = [];
                $pagerURL = $this->URL;
                $elementsOnPage = 30;


                if ($elementsCount = $this->getService('db')->table('module_zxmusic')->count('id')) {
                    $page = 0;
                    $controller = controller::getInstance();
                    if ($controller->getParameter('page')) {
                        $page = intval($controller->getParameter('page'));
                    }

                    $pager = new pager($pagerURL, $elementsCount, $elementsOnPage, $page, 'page');
                    $this->pager = $pager;

                    $picturesIdFilter = [];
                    if ($records = $this->getService('db')->table('module_zxmusic')->select('id')->orderBy(
                        'id',
                        'desc'
                    )->offset($pager->startElement)->limit($elementsOnPage)->get()) {
                        foreach ($records as $record) {
                            $picturesIdFilter[] = $record['id'];
                        }
                    }

                    $this->musicPageList = $structureManager->getElementsByIdList($picturesIdFilter, $this->id, true);

                    $sort = [];
                    foreach ($this->musicPageList as $element) {
                        $sort[] = $element->id;
                    }
                    array_multisort($sort, SORT_DESC, $this->musicPageList);
                }
            }
            return $this->musicPageList;
        }
        return [];
    }

    public function getPartiesList()
    {
        if (is_null($this->partiesList)) {
            $structureManager = $this->getService('structureManager');
            $partiesFolder = $structureManager->getElementByMarker('parties');
            $this->partiesList = $structureManager->getElementsChildren($partiesFolder->id);

            foreach ($this->partiesList as $year) {
                $structureManager->getElementsChildren($year->id);
                $sortParameter = [];
                foreach ($year->childrenList as $child) {
                    $sortParameter[] = mb_strtolower($child->title);
                }
                array_multisort($sortParameter, SORT_ASC, $year->childrenList);
            }
        }
        return $this->partiesList;
    }

    public function getGamesList()
    {
        if (is_null($this->gamesList)) {
            $structureManager = $this->getService('structureManager');
            $gamesFolder = $structureManager->getElementByMarker('games');
            $this->gamesList = $structureManager->getElementsChildren($gamesFolder->id);

            foreach ($this->gamesList as $year) {
                $structureManager->getElementsChildren($year->id);
                $sortParameter = [];
                foreach ($year->childrenList as $child) {
                    $sortParameter[] = mb_strtolower($child->title);
                }
                array_multisort($sortParameter, SORT_ASC, $year->childrenList);
            }
        }
        return $this->gamesList;
    }

    public function getAuthorsList()
    {
        if (is_null($this->authorsList)) {
            $structureManager = $this->getService('structureManager');
            $authorsFolder = $structureManager->getElementByMarker('authors');
            $this->authorsList = $structureManager->getElementsChildren($authorsFolder->id);
            $this->authorsIDList = $this->getService('linksManager')
                ->getConnectedIdList($this->id, 'authorPicture', 'child');

            foreach ($this->authorsList as $author) {
                $structureManager->getElementsChildren($author->id);
                $sortParameter = [];
                foreach ($author->childrenList as $child) {
                    $sortParameter[] = mb_strtolower($child->title);
                }
                array_multisort($sortParameter, SORT_ASC, $author->childrenList);
            }
        }
        return $this->authorsList;
    }

    public function getAuthorsIDList()
    {
        if (is_null($this->authorsIDList)) {
            $this->authorsIDList = $this->getService('linksManager')
                ->getConnectedIdList($this->id, 'authorPicture', 'child');
        }
        return $this->authorsIDList;
    }

}


