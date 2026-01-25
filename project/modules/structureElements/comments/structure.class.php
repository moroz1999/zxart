<?php

class commentsElement extends structureElement
{
    use AutoMarkerTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    public $pager;
    protected $allowedTypes = [
        'comment',
    ];
    const LIST_SIZE = 50;
    public $comments = [];

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['author'] = 'text';
        $moduleStructure['email'] = 'text';
        $moduleStructure['ipAddress'] = 'text';
        $moduleStructure['periodStart'] = 'dateTime';
        $moduleStructure['periodEnd'] = 'dateTime';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    protected function getTabsList()
    {
        return [
            'show',
            'showForm',
            'showLayoutForm',
            'showPrivileges',
        ];
    }

    public function getComments(&$filters)
    {
        $comments = [];
        $db = $this->getService('db');
        $query = $db->table('module_comment');
        $query->leftJoin('structure_elements', 'module_comment.id', '=', 'structure_elements.id');
        if ($filters['author']) {
            $query->where('author', 'like', '%' . $filters['author'] . '%');
        }
        if ($filters['ipAddress']) {
            $query->where('ipAddress', '=', $filters['ipAddress']);
        }
        if ($filters['periodStart']) {
            $query->where('structure_elements.dateCreated', '>=', $filters['periodStart']);
        }
        if ($filters['periodEnd']) {
            $query->where('structure_elements.dateCreated', '<=', $filters['periodEnd']);
        }

        // set pager
        $pagerURL = $this->URL;
        $elementsCount = $query->count('module_comment.id');
        $page = 0;

        $controller = controller::getInstance();
        if ($controller->getParameter('page')) {
            $page = intval($controller->getParameter('page'));
        }
        $pager = new pager($pagerURL, $elementsCount, self::LIST_SIZE, $page, 'page');
        $this->pager = $pager;

        $query->offset($pager->startElement);
        $query->limit(self::LIST_SIZE);
        $query->orderBy('structure_elements.dateCreated', 'desc');
        $query->select('module_comment.id');

        // submit query
        $results = (array)$query->get();
        $structureManager = $this->getService('structureManager');
        foreach ($results as &$result) {
            if ($commentElement = $structureManager->getElementById($result['id'])) {
                $comments[] = $commentElement;
            }
        }
        return $comments;
    }
}
