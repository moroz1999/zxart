<?php

class groupsListElement extends structureElement
{
    use LettersElementsListProviderTrait;

    public $dataResourceName = 'module_groupslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $lettersSelectorInfo;

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
}
