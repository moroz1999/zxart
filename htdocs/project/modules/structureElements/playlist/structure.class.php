<?php

class playlistElement extends structureElement
{
    public $dataResourceName = 'module_playlist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $picturesList;
    protected $musicList;
    protected $zxProdsList;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['userId'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
    }

    public function getElementData()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->URL,
        ];
    }

    public function getPicturesList()
    {
        if ($this->picturesList === null) {
            $this->picturesList = [];
            $structureManager = $this->getService('structureManager');
            if ($connectedElements = $structureManager->getElementsChildren($this->id, null, 'playlist')) {
                foreach ($connectedElements as $element) {
                    if ($element->structureType == 'zxPicture') {
                        $this->picturesList[] = $element;
                    }
                }
            }
        }
        return $this->picturesList;
    }

    public function getMusicList()
    {
        if ($this->musicList === null) {
            $this->musicList = [];
            $structureManager = $this->getService('structureManager');
            if ($connectedElements = $structureManager->getElementsChildren($this->id, null, 'playlist')) {
                foreach ($connectedElements as $element) {
                    if ($element->structureType == 'zxMusic') {
                        $this->musicList[] = $element;
                    }
                }
            }
        }
        return $this->musicList;
    }

    public function getZxProdsList()
    {
        if ($this->zxProdsList === null) {
            $this->zxProdsList = [];
            $structureManager = $this->getService('structureManager');
            if ($connectedElements = $structureManager->getElementsChildren($this->id, null, 'playlist')) {
                foreach ($connectedElements as $element) {
                    if ($element->structureType == 'zxProd') {
                        $this->zxProdsList[] = $element;
                    }
                }
            }
        }
        return $this->zxProdsList;
    }
}


