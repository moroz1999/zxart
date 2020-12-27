<?php

class tagsListElement extends structureElement
{
    public $dataResourceName = 'module_tagslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'container';

    protected $tagsList;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getAllTags()
    {
        if ($this->tagsList === null) {
            $apiQueriesManager = $this->getService('ApiQueriesManager');
            $sectionsLogics = $this->getService('SectionLogics');;
            if (($type = $sectionsLogics->getArtItemsType()) == 'graphics') {
                $parameters = [
                    'zxPictureAll' => true,
                ];
            } elseif ($type == 'music') {
                $parameters = [
                    'zxMusicAll' => true,
                ];
            } else {
                $parameters = [];
            }


            $query = $apiQueriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setOrder(['title' => 'asc']);
            $query->setExportType('tag');
            if ($result = $query->getQueryResult()) {
                $this->tagsList = $result['tag'];
            }

            $sort = [];
            $maxAmount = 0;
            foreach ($this->tagsList as $tag) {
                $sort[] = strtolower($tag->title);
                if ($tag->amount > $maxAmount) {
                    $maxAmount = $tag->amount;
                }
            }
            if ($maxAmount > $this->getService('ConfigManager')->get('zx.maxTagsAmount')) {
                $maxAmount = $this->getService('ConfigManager')->get('zx.maxTagsAmount');
            }
            $this->maxAmount = $maxAmount;
            array_multisort($sort, SORT_ASC, $this->tagsList);
        }
        return $this->tagsList;
    }
}


