<?php

class batchUploadFormZxProdsUploadForm extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdsUploadFormElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('uploadForm');
            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                if ($parentElement->structureType === 'author' || $parentElement->structureType === 'group'
                    || $parentElement->structureType === 'authorAlias' || $parentElement->structureType === 'groupAlias') {
                    $structureElement->publishers = [$parentElement];
                } elseif ($parentElement->structureType == 'party') {
                    $structureElement->party = $parentElement->id;
                }
            }
        }
    }
}