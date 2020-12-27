<?php

class picturesCatalogueElement extends structureElement
{
    use GraphicsCompoProvider;

    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $allowedTypes = ['zxPicture'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected $picturesPageList;

    public $pager;
    public $picturesList;

    protected $gamesList;
    protected $partiesList;
    protected $authorsList;
    protected $authorsIDList;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';

        $moduleStructure['description'] = 'textarea';
        $moduleStructure['border'] = 'text';
        $moduleStructure['party'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['game'] = 'text';
        $moduleStructure['author'] = 'numbersArray';
        $moduleStructure['type'] = 'text';
        $moduleStructure['year'] = 'text';

        $moduleStructure['image'] = 'files';

        $moduleStructure['tagsText'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $useBlackList = false)
    {
        if ($roles != 'container') {
            if (is_null($this->picturesPageList)) {
                $structureManager = $this->getService('structureManager');
                $this->picturesPageList = [];
                $pagerURL = $this->URL;
                $elementsOnPage = 30;

                if ($elementsCount = $this->getService('db')->table('module_zxpicture')->count('id')) {
                    $page = 0;
                    $controller = controller::getInstance();
                    if ($controller->getParameter('page')) {
                        $page = intval($controller->getParameter('page'));
                    }

                    $pager = new pager($pagerURL, $elementsCount, $elementsOnPage, $page, 'page');
                    $this->pager = $pager;

                    $picturesIdFilter = [];
                    if ($records = $this->getService('db')->table('module_zxpicture')->select('id')->orderBy(
                        'id',
                        'desc'
                    )->offset($pager->startElement)->limit($elementsOnPage)->get()) {
                        foreach ($records as $record) {
                            $picturesIdFilter[] = $record['id'];
                        }
                    }

                    $this->picturesPageList = $structureManager->getElementsByIdList(
                        $picturesIdFilter,
                        $this->id,
                        true
                    );

                    $sort = [];
                    foreach ($this->picturesPageList as $element) {
                        $sort[] = $element->id;
                    }
                    array_multisort($sort, SORT_DESC, $this->picturesPageList);
                }
            }
            return $this->picturesPageList;
        }
        return [];
    }
}


