<?php

class receiveTag extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }

            if ($structureElement->joinTag != "") {
                $tagsManager = $this->getService('tagsManager');
                $tagsManager->joinTags($structureElement->id, $structureElement->joinTag);
            }

            $structureElement->persistElementData();
            $structureElement->updateAmount();

            $controller->restart($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'structureName',
            'title',
            'synonym',
            'description',
            'joinTag',
            'verified',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


