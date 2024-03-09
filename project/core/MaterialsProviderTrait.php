<?php

trait MaterialsProviderTrait
{
    protected $pictures;
    protected $tunes;
    protected $materialsList;

    public function getPictures()
    {
        if ($this->pictures === null) {
            $this->pictures = [];
            if ($materials = $this->getMaterialsList()) {
                foreach ($materials as $item) {
                    if ($item->structureType == 'zxPicture') {
                        $this->pictures[] = $item;
                    }
                }
            }
        }
        return $this->pictures;
    }

    public function getTunes()
    {
        if ($this->tunes === null) {
            $this->tunes = [];
            if ($materials = $this->getMaterialsList()) {
                foreach ($materials as $item) {
                    if ($item->structureType == 'zxMusic') {
                        $this->tunes[] = $item;
                    }
                }
            }
        }
        return $this->tunes;
    }

    public function getMaterialsList()
    {
        if ($this->materialsList === null) {
            if ($ids = $this->getService('linksManager')->getConnectedIdList($this->id, 'gameLink', 'parent')) {
                $structureManager = $this->getService('structureManager');
                foreach ($ids as $id) {
                    if ($element = $structureManager->getElementById($id)) {
                        $this->materialsList[] = $element;
                    }
                }
            }
        }
        return $this->materialsList;
    }
}