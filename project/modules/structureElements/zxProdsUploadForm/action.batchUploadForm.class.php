<?php

class batchUploadFormZxProdsUploadForm extends structureElementAction
{
    /**
     * @param zxProdsUploadFormElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setViewName('uploadForm');
            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                if ($parentElement->structureType === 'author' || $parentElement->structureType === 'group'
                    || $parentElement->structureType === 'authorAlias' || $parentElement->structureType === 'groupAlias') {
                    $structureElement->publishers = [$parentElement];
                } elseif ($parentElement->structureType === 'party') {
                    $structureElement->party = $parentElement->getId();
                }
            }
        }
    }
}