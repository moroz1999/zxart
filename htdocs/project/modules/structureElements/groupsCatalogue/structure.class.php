<?php

class groupsCatalogueElement extends structureElement
{
    public $dataResourceName = 'module_groupscatalogue';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'container';

    protected $groupsCatalogue;
    protected $replacementElements;
    protected $lettersSelectorInfo;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['items'] = 'text';
    }

    public function getReplacementElements($roles)
    {
        return [];
    }

    public function getReplacementElements2($roles)
    {
        if (!in_array('content', (array)$roles)) {
            $structureManager = $this->getService('structureManager');
            if ($structureManager->getRootElementMarker() === $this->getService('ConfigManager')->get(
                    'main.rootMarkerAdmin'
                )) {
                return false;
            }
            if ($this->replacementElements === null) {
                if ($firstParent = $structureManager->getElementsFirstParent($this->id)) {
                    $this->replacementElements = $structureManager->getElementsChildren(
                        $firstParent->id,
                        'container',
                        'groupsCatalogue'
                    );

                    foreach ($this->replacementElements as $letter) {
                        $letter->columns = $this->columns;
                        $letter->setViewName('groups');
                        $letter->template = 'letter.groups.tpl';
                    }
                }
            }

            return $this->replacementElements;
        } else {
            return false;
        }
    }

    public function deleteElementData()
    {
        $structureManager = $this->getService('structureManager');
        if ($firstParent = $structureManager->getElementsFirstParent($this->id)) {
            $linksManager = $this->getService('linksManager');
            $linksList = $linksManager->getElementsLinks(
                $firstParent->id,
                'groupsCatalogue',
                'parent'
            );
            foreach ($linksList as $link) {
                $linksManager->unLinkElements(
                    $firstParent->id,
                    $link->childStructureId,
                    'groupsCatalogue'
                );
            }
        }
        parent::deleteElementData();
    }

    public function getLettersSelectorInfo()
    {
        if ($this->lettersSelectorInfo === null) {
            $this->lettersSelectorInfo = [];
            if ($letters = $this->getReplacementElements2(null)) {
                foreach ($letters as $letter) {
                    $this->lettersSelectorInfo[] = [
                        'url' => $letter->getUrl(),
                        'title' => $letter->title,
                    ];
                }
            }
        }
        return $this->lettersSelectorInfo;
    }
}