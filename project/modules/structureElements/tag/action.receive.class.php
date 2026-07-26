<?php

class receiveTag extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param tagElement $structureElement
     */
    #[\Override]
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }

            if ($structureElement->joinTag != "") {
                $tagsManager = $this->getService(tagsManager::class);
                $tagsManager->joinTags($structureElement->getId(), $structureElement->joinTag);
            }

            $structureElement->persistElementData();

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


