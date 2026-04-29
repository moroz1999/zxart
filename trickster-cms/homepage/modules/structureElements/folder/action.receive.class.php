<?php

class receiveFolder extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param folderElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->getPersistedId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }
            $structureElement->persistElementData();

            $structureElement->persistDisplayMenusLinks();

            $controller->redirect($structureElement->URL);
        }
        $structureElement->executeAction("showForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'marker',
            'title',
            'image',
            'hidden',
            'columns',
            'displayMenus',
            'externalUrl',
        ];
    }

    public function setValidators(&$validators)
    {
        $validators['title'][] = 'notEmpty';
    }
}