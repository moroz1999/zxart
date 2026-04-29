<?php

class receiveLinkList extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param linkListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            $structureElement->structureName = $structureElement->title;
            if ($structureElement->getDataChunk("image")->originalName) {
                $structureElement->image = $structureElement->id . '_1';
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $structureElement->persistElementData();

            $structureElement->persistDisplayMenusLinks();
            //$controller->redirect($structureElement->URL);
        }
        if ($controller->getApplicationName() != 'adminAjax') {
            $controller->redirect($structureElement->URL);
            //$structureElement->executeAction("showForm");
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'image',
            'marker',
            'content',
            'displayMenus',
            'fixedId',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}