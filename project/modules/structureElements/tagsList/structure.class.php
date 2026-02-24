<?php

class tagsListElement extends structureElement
{
    public $dataResourceName = 'module_tagslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'container';
    public $maxAmount = 0;

    /**
     * @var tagElement[]
     */
    protected $tagsList;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    /**
     * @return structureElement[]
     *
     * @psalm-return array<structureElement>
     */
    public function getAllTags(): array
    {
        if ($this->tagsList === null) {
            /**
             * @var ApiQueriesManager $apiQueriesManager
             */
            $apiQueriesManager = $this->getService(ApiQueriesManager::class);
            $sectionsLogics = $this->getService(SectionLogics::class);
            if (($type = $sectionsLogics->getArtItemsType()) === 'graphics') {
                $parameters = [
                    'zxPictureAll' => true,
                ];
            } elseif ($type === 'music') {
                $parameters = [
                    'zxMusicAll' => true,
                ];
            } elseif ($type === 'software') {
                $parameters = [
                    'zxProdAll' => true,
                ];
            } else {
                $parameters = [];
            }

            $query = $apiQueriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setOrder(['title' => 'asc']);
            $query->setExportType('tag');
            if ($queries = $query->getFilterQueries()) {
                $result = $queries['tag']->get();
                $idList = array_column($result, 'id');
                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                $this->tagsList = $structureManager->getElementsByIdList($idList, $this->getId(), true);
            }

            $sort = [];
            $maxAmount = 0;
            foreach ($this->tagsList as $tag) {
                $sort[] = strtolower($tag->title);
                if ($tag->amount > $maxAmount) {
                    $maxAmount = $tag->amount;
                }
            }
            if ($maxAmount > $this->getService(ConfigManager::class)->get('zx.maxTagsAmount')) {
                $maxAmount = $this->getService(ConfigManager::class)->get('zx.maxTagsAmount');
            }
            $this->maxAmount = $maxAmount;
            array_multisort($sort, SORT_ASC, $this->tagsList);
        }
        return $this->tagsList;
    }
}