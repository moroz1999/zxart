<?php

/**
 * Class yearElement
 * @property string $title
 */
class yearElement extends structureElement implements ColumnsTypeProvider
{
    public $dataResourceName = 'module_generic';
    public $allowedTypes = ['party'];
    public $defaultActionName = 'show';
    public $role = 'container';
    protected $partiesList;
    protected $yearsSelectorInfo;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getPartiesList()
    {
        if (is_null($this->partiesList)) {
            $linksManager = $this->getService('linksManager');
            $idList = $linksManager->getConnectedIdList($this->getId(), 'structure', 'parent');

            $queriesManager = $this->getService('ApiQueriesManager');

            $parameters = [
                'partyId' => $idList,
            ];

            $query = $queriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setExportType('party');
            $query->setOrder(['title' => 'asc']);
            if ($result = $query->getQueryResult()) {
                $this->partiesList = $result['party'];
            }
        }
        return $this->partiesList;
    }

    /**
     * @return void
     */
    public function persistElementData()
    {
        parent::persistElementData();
        $this->updateCataloguesLinks();
    }

    public function updateCataloguesLinks(): void
    {
        $structureManager = $this->getService('structureManager');
        if ($partiesCatalogues = $structureManager->getElementsByType('partiesCatalogue')) {
            $linksManager = $this->getService('linksManager');
            foreach ($partiesCatalogues as $partiesCatalogue) {
                if ($firstParent = $structureManager->getElementsFirstParent($partiesCatalogue->id)) {
                    $linksManager->linkElements($firstParent->id, $this->getId(), 'partiesCatalogue');
                }
            }
        }
    }

    public function getColumnsType()
    {
        return $this->columns;
    }

    public function getYearsSelectorInfo()
    {
        if ($this->yearsSelectorInfo === null) {
            $this->yearsSelectorInfo = [];
            $structureManager = $this->getService('structureManager');
            if ($firstParent = $structureManager->getElementsFirstParent($this->getId())) {
                $years = $structureManager->getElementsChildren($firstParent->id, 'container', 'partiesCatalogue');

                foreach ($years as $year) {
                    if ($year->structureType == 'year') {
                        $this->yearsSelectorInfo[] = [
                            'url' => $year->getUrl(),
                            'title' => $year->title,
                        ];
                    }
                }
            }
        }
        return $this->yearsSelectorInfo;
    }
}

