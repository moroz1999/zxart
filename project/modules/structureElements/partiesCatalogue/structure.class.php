<?php

class partiesCatalogueElement extends structureElement
{
    public $dataResourceName = 'module_partiescatalogue';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'container';

    protected $partiesCatalogue;
    protected $replacementElements;
    protected $yearsSelectorInfo;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['items'] = 'text';
    }

    /**
     * @psalm-return array<never, never>
     */
    public function getReplacementElements($roles): array
    {
        return [];
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