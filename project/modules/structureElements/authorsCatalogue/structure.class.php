<?php

class authorsCatalogueElement extends structureElement
{
    public $dataResourceName = 'module_authorscatalogue';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'container';

    protected $authorsCatalogue;
    protected $replacementElements;
    protected $lettersSelectorInfo;

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

    public function getReplacementElements2($roles)
    {
        if (!in_array('content', (array)$roles)) {
            $structureManager = $this->getService('structureManager');
            if ($structureManager->getRootElementMarker() === $this->getService(ConfigManager::class)->get(
                    'main.rootMarkerAdmin'
                )) {
                return false;
            }
            if ($this->replacementElements === null) {
                if ($firstParent = $structureManager->getElementsFirstParent($this->getId())) {
                    $this->replacementElements = $structureManager->getElementsChildren(
                        $firstParent->id,
                        'container',
                        'authorsCatalogue'
                    );

                    foreach ($this->replacementElements as $letter) {
                        $letter->columns = $this->columns;
                        $letter->setViewName('authors');
                        $letter->template = 'letter.authors.tpl';
                    }
                }
            }

            return $this->replacementElements;
        } else {
            return false;
        }
    }

    /**
     * @return void
     */
    public function deleteElementData()
    {
        $structureManager = $this->getService('structureManager');
        if ($firstParent = $structureManager->getElementsFirstParent($this->getId())) {
            $linksManager = $this->getService(linksManager::class);
            $linksList = $linksManager->getElementsLinks(
                $firstParent->id,
                'authorsCatalogue',
                'parent'
            );
            foreach ($linksList as $link) {
                $linksManager->unLinkElements(
                    $firstParent->id,
                    $link->childStructureId,
                    'authorsCatalogue'
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
                    if ($letter->structureType == 'letter') {
                        $this->lettersSelectorInfo[] = [
                            'url' => $letter->getUrl(),
                            'title' => $letter->title,
                        ];
                    }
                }
            }
        }
        return $this->lettersSelectorInfo;
    }
}