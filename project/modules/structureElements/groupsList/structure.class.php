<?php

class groupsListElement extends structureElement
{
    public $dataResourceName = 'module_groupslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
        $moduleStructure['items'] = 'text';
    }

    public function getLatestGroups($limit = 300)
    {
        static $groups;
        if (is_null($groups)) {
            $groups = [];

            $apiQueriesManager = $this->getService(ApiQueriesManager::class);

            $parameters = [];

            $query = $apiQueriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setOrder(['date' => 'desc']);
            $query->setLimit($limit);
            $query->setExportType('group');
            if ($result = $query->getQueryResult()) {
                $groups = $result['group'];
            }
        }
        return $groups;
    }
}
