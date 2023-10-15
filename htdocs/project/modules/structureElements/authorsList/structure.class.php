<?php

class authorsListElement extends structureElement
{
    use LettersElementsListProviderTrait;

    public $dataResourceName = 'module_authorslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $lettersSelectorInfo;
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
        $moduleStructure['items'] = 'text';
    }

    public function getLatestAuthors($limit = 300)
    {
        static $authors;
        if (is_null($authors)) {
            $authors = [];

            $apiQueriesManager = $this->getService('ApiQueriesManager');

            $parameters = [
                'authorOfItemType' => $this->getAuthorTypes(),
            ];

            $query = $apiQueriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setOrder(['date' => 'desc']);
            $query->setLimit($limit);
            $query->setExportType('author');
            if ($result = $query->getQueryResult()) {
                $authors = $result['author'];
            }
        }
        return $authors;
    }

    protected function getAuthorTypes()
    {
        if ($this->items == 'music') {
            return ['authorMusic'];
        } else {
            if ($this->items == 'all') {
                return ['authorMusic', 'authorPicture'];
            } else {
                return ['authorPicture'];
            }
        }
    }

    public function getTopAuthors($limit = 10)
    {
        static $authors;
        if (is_null($authors)) {
            $authors = [];

            $apiQueriesManager = $this->getService('ApiQueriesManager');

            $parameters = [
                'authorOfItemType' => $this->getAuthorTypes(),
            ];

            $query = $apiQueriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            if ($this->items == 'graphics') {
                $query->setOrder(['graphicsRating' => 'desc']);
            } else {
                if ($this->items == 'music') {
                    $query->setOrder(['musicRating' => 'desc']);
                }
            }
            $query->setLimit($limit);
            $query->setExportType('author');
            if ($result = $query->getQueryResult()) {
                $authors = $result['author'];
            }
        }
        return $authors;
    }

    public function getActiveAuthors()
    {
        static $authors;
        if ($authors === null) {
            $year = date('Y');
            $years = [];
            for ($i = 0; $i < 2; $i++) {
                $years[] = $year - $i;
            }

            $queriesManager = $this->getService('ApiQueriesManager');

            $parameters = [];
            if ($this->items == 'music') {
                $parameters['zxMusicYear'] = $years;
            } else {
                $parameters['zxPictureYear'] = $years;
                $parameters['zxPictureNotType'] = 'attributes';
            }

            $query = $queriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setExportType('author');
            $query->setOrder(['title' => 'asc']);
            if ($result = $query->getQueryResult()) {
                $authors = $result['author'];
            }
        }
        return $authors;
    }

    public function getLetterAuthors()
    {
        static $authors;
        if ($authors === null) {
            $controllerApplication = $this->getService('controllerApplication');
            if ($letterName = $controllerApplication->getParameter('letter')) {
                if ($letters = $this->getLetterElements()) {
                    foreach ($letters as $letter) {
                        if ($letter->structureName == $letterName) {
                            $authors = $letter->getAuthorsList();
                        }
                    }
                }
            }
        }
        return $authors;
    }

    public function getLettersSelectorInfo()
    {
        if ($this->lettersSelectorInfo === null) {
            $this->lettersSelectorInfo = [];
            if ($letters = $this->getLetterElements()) {
                foreach ($letters as $letter) {
                    $this->lettersSelectorInfo[] = [
                        'url' => $this->getUrl() . 'letter:' . $letter->structureName . '/',
                        'title' => $letter->title,
                    ];
                }
            }
        }

        return $this->lettersSelectorInfo;
    }

    protected function getLettersListMarker($type)
    {
        if ($type == 'admin') {
            return 'authors';
        } else {
            return 'authorsmenu';
        }
    }
}
