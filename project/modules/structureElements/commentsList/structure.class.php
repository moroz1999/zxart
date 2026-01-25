<?php

class commentsListElement extends structureElement
{
    public $dataResourceName = 'module_commentslist';
    protected $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $pager;
    protected $comments;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
    }

    public function getPager()
    {
        if ($this->pager === null) {
            $this->getCommentsList();
        }
        return $this->pager;
    }

    public function getParentUrl()
    {
        return $this->getFirstParentElement()->getUrl();
    }

    public function getCommentsList()
    {
        if ($this->comments === null) {
            $db = $this->getService('db');
            $this->comments = [];
            $controller = controller::getInstance();

            $currentPage = 1;
            if ($controller->getParameter('page')) {
                $currentPage = intval($controller->getParameter('page'));
            }
            $elementsOnPage = 50;
            $startElement = ($currentPage - 1) * $elementsOnPage;

            $baseURL = $this->URL;

            $structureManager = $this->getService('structureManager');
            if ($count = $db
                ->table('structure_elements')
                ->where('structureType', '=', 'comment')
                ->count('id')) {
                $this->pager = new pager($baseURL, $count, $elementsOnPage, $currentPage);


                if ($result = $db
                    ->table('structure_elements')
                    ->where('structureType', '=', 'comment')
                    ->orderBy('dateCreated', 'desc')
                    ->offset($startElement)
                    ->limit($elementsOnPage)
                    ->select('id')
                    ->get()
                ) {
                    foreach ($result as &$row) {
                        if ($comment = $structureManager->getElementById($row['id'])) {
                            $this->comments[] = $comment;
                        }
                    }
                }
            }
        }

        return $this->comments;
    }
}