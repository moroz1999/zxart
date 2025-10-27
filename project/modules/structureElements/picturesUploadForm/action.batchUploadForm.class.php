<?php

class batchUploadFormPicturesUploadForm extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param picturesUploadFormElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('uploadForm');
            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                if ($parentElement->structureType === 'author' || $parentElement->structureType === 'authorAlias') {
                    $structureElement->author = [$parentElement->getId()];
                } elseif ($parentElement->structureType === 'party') {
                    $structureElement->party = $parentElement->getId();
                } elseif ($parentElement->structureType === 'zxProd' || $parentElement->structureType === 'zxRelease') {
                    $structureElement->game = $parentElement->getId();
                }
            }
        }
    }
}