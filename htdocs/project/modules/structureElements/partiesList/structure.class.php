<?php

class partiesListElement extends structureElement
{
    use CacheOperatingElement;

    public $dataResourceName = 'module_partieslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected $latestParties;
    protected $recentParties;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
    }

    public function getLatestParties($limit = 10)
    {
        if ($this->latestParties === null) {
            $this->latestParties = [];

            $structureManager = $this->getService('structureManager');
            /**
             * @var \Illuminate\Database\Capsule\Manager $db
             */
            $db = $this->getService('db');
            $now = time();

            $query = $db->table('structure_elements')
                ->where('dateCreated', '<=', $now)
                ->where('structureType', '=', 'party');
            $query->orderBy("dateCreated", 'desc')->limit($limit);

            if ($result = $query->get(['id'])) {
                foreach ($result as $row) {
                    if ($party = $structureManager->getElementById($row['id'])) {
                        $this->latestParties[] = $party;
                    }
                }
            }
        }
        return $this->latestParties;
    }

    public function getRecentParties($limit = 5)
    {
        if ($this->recentParties === null) {
            $cache = $this->getElementsListCache('rp', 60 * 60 * 3);
            if (($this->recentParties = $cache->load()) === false) {
                $this->recentParties = [];

                $structureManager = $this->getService('structureManager');
                /**
                 * @var \Illuminate\Database\Capsule\Manager $db
                 */
                $db = $this->getService('db');
                $query = $db->table('module_party AS parties')
                    ->leftJoin('structure_elements AS partystruct', 'partystruct.id', '=', 'parties.id')
                    ->leftJoin(
                        'structure_links AS links',
                        function ($join) {
                            $join->on('links.childStructureId', '=', 'parties.id')
                                ->where('links.type', '=', 'structure');
                        }
                    )
                    ->leftJoin('structure_elements AS el2', 'el2.id', '=', 'links.parentStructureId')
                    ->orderBy("el2.structureName", 'desc')
                    ->orderBy("partystruct.dateCreated", 'desc')
                    ->limit($limit);
                if ($result = $query->get(['parties.id'])) {
                    foreach ($result as $row) {
                        if ($party = $structureManager->getElementById($row['id'])) {
                            $this->recentParties[] = $party;
                        }
                    }
                }
                $cache->save($this->recentParties);
            }
        }
        return $this->recentParties;
    }
}