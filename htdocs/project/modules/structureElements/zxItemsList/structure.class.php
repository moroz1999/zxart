<?php

class zxItemsListElement extends structureElement
{
    use CacheOperatingElement;

    public $dataResourceName = 'module_zxitemslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected $itemsList;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
        $moduleStructure['items'] = 'text';
        $moduleStructure['apiString'] = 'text';
        $moduleStructure['searchFormParametersString'] = 'text';
        $moduleStructure['layout'] = 'text';
        $moduleStructure['requiresUser'] = 'checkbox';
    }

    public function getItemsList()
    {
        if ($this->itemsList === null) {
            $cache = $this->getElementsListCache('il', 60 * 60 * 7);
            if (($this->itemsList = $cache->load()) === false) {
                $structureType = '';
                if ((!$this->items) || $this->items == 'graphics') {
                    $structureType = 'zxPicture';
                } elseif ($this->items == 'music') {
                    $structureType = 'zxMusic';
                } elseif ($this->items == 'zxProd') {
                    $structureType = 'zxProd';
                }
                /**
                 * @var ApiQueriesManager $apiQueriesManager
                 */
                $apiQueriesManager = $this->getService('ApiQueriesManager');
                if ($apiQuery = $apiQueriesManager->getQueryFromString($this->apiString)) {
                    if ($result = $apiQuery->getQueryResult()) {
                        $this->itemsList = $result[$structureType];
                    }
                }
                $cache->save($this->itemsList);
            }
        }
        return $this->itemsList;
    }
}


