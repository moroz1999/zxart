<?php

trait Location
{
    protected $locationMode;
    protected $authorsList;
    protected $groupsList;
    protected $partiesList;
    protected $amounts = [];

    public function getAmountInLocation($type = null)
    {
        if (!$type) {
            $type = $this->getLocationMode();
        }
        if (!isset($this->amounts[$type])) {
            /**
             * @var \Illuminate\Database\Connection $db
             */
            $db = $this->getService('db');
            $this->amounts[$type] = $db->table('module_' . $type)->where(
                $this->locationPropertyName,
                '=',
                $this->id
            )->selectRaw('count(distinct(id)) AS amount')->value('amount');
        }
        return $this->amounts[$type];
    }

    public function getLocationMode()
    {
        if (!$this->locationMode) {
            if ($value = controller::getInstance()->getParameter('show')) {
                if (in_array($value, ['author', 'group', 'party'])) {
                    $this->locationMode = $value;
                }
            }
        }
        if (!$this->locationMode) {
            $this->locationMode = 'author';
        }
        return $this->locationMode;
    }

    public function getAuthorsList()
    {
        if ($this->authorsList === null) {
            $key = 'auth';
            $cache = $this->getElementsListCache($key, 60 * 60 * 24 * 7);
            if (($this->authorsList = $cache->load()) === null) {
                $this->authorsList = [];
                /**
                 * @var ApiQueriesManager $queriesManager
                 */
                $queriesManager = $this->getService('ApiQueriesManager');

                $parameters = [
                    'author' . ucfirst($this->locationPropertyName) => [$this->id],
                ];
                $query = $queriesManager->getQuery()
                    ->setFiltrationParameters($parameters)
                    ->setExportType('author')
                    ->setOrder(['title' => 'asc'])
                    ->setResultTypes(['author']);
                if ($result = $query->getQueryResult()) {
                    $this->authorsList = $result['author'];
                }

                $cache->save($this->authorsList);
            }
        }
        return $this->authorsList;
    }

    public function getGroupsList()
    {
        if ($this->groupsList === null) {
            $key = 'grp';
            $cache = $this->getElementsListCache($key, 60 * 60 * 24 * 7);
            if (($this->groupsList = $cache->load()) === null) {
                $this->groupsList = [];
                /**
                 * @var ApiQueriesManager $queriesManager
                 */
                $queriesManager = $this->getService('ApiQueriesManager');

                $parameters = [
                    'group' . ucfirst($this->locationPropertyName) => [$this->id],
                ];
                $query = $queriesManager->getQuery()
                    ->setFiltrationParameters($parameters)
                    ->setExportType('group')
                    ->setOrder(['title' => 'asc'])
                    ->setResultTypes(['group']);
                if ($result = $query->getQueryResult()) {
                    $this->groupsList = $result['group'];
                }

                $cache->save($this->groupsList);
            }
        }
        return $this->groupsList;
    }

    public function getPartiesList()
    {
        if ($this->partiesList === null) {
            $key = 'prt';
            $cache = $this->getElementsListCache($key, 60 * 60 * 24 * 7);
            if (($this->partiesList = $cache->load()) === null) {
                $this->partiesList = [];
                /**
                 * @var ApiQueriesManager $queriesManager
                 */
                $queriesManager = $this->getService('ApiQueriesManager');

                $parameters = [
                    'party' . ucfirst($this->locationPropertyName) => [$this->id],
                ];
                $query = $queriesManager->getQuery()
                    ->setFiltrationParameters($parameters)
                    ->setExportType('party')
                    ->setOrder(['title' => 'asc'])
                    ->setResultTypes(['party']);
                if ($result = $query->getQueryResult()) {
                    $this->partiesList = $result['party'];
                }

                $cache->save($this->partiesList);
            }
        }
        return $this->partiesList;
    }

    public function getUrl($locationMode = null, $action = null)
    {
        if ($locationMode === null) {
            $locationMode = $this->getLocationMode();
        }
        if ($locationMode === 'author') {
            return $this->URL;
        }
        return $this->URL . 'show:' . $locationMode . '/';
    }

    public function matchesTitle($title): bool
    {
        if ($languages = $this->getLanguagesList()) {
            foreach ($languages as $languageId) {
                $languageTitle = $this->getValue('title', $languageId);
                if (trim($languageTitle) === trim($title)) {
                    return true;
                }
            }
        }
        return false;
    }
}