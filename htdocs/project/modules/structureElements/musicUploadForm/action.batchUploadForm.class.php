<?php

class batchUploadFormMusicUploadForm extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param musicUploadFormElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('uploadForm');
            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                if ($parentElement->structureType === 'author' || $parentElement->structureType === 'authorAlias') {
                    $structureElement->author = [$parentElement->id];
                } elseif ($parentElement->structureType === 'party') {
                    $structureElement->party = $parentElement->id;
                } elseif ($parentElement->structureType === 'zxProd' || $parentElement->structureType === 'zxRelease') {
                    $structureElement->game = $parentElement->id;
                }
            }
        }
    }
}