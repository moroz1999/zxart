<?php

class receiveTag extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }

            if ($structureElement->joinTag != "") {
                $tagsManager = $this->getService('tagsManager');
                $tagsManager->joinTags($structureElement->getId(), $structureElement->joinTag);
            }

            $structureElement->persistElementData();
            $structureElement->updateAmount();

            $controller->restart($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
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

    public function setValidators(&$validators): void
    {
    }
}


