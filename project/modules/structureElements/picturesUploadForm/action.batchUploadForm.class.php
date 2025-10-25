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
            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->getId())) {
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