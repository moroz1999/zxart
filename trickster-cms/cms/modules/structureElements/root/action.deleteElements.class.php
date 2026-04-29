<?php

class deleteElementsRoot extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param rootElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->executeAction('showFullList');

        if ($this->validated) {
            $elements = $structureElement->elements;
            foreach ($elements as $elementID => $value) {
                if ($deletedElement = $structureManager->getElementById($elementID)) {
                    $deletedElement->groupDeletion = true;
                    $deletedElement->executeAction('delete');
                }
            }
        }
        $controller->restart();
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['elements'];
    }
}