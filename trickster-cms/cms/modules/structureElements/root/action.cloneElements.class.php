<?php

class cloneElementsRoot extends structureElementAction
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
            foreach ($elements as $elementID => &$value) {
                if ($clonedElement = $structureManager->getElementById($elementID)) {
                    $clonedElement->executeAction('clone');
                }
            }
        }
        // TODO: investigate what's wrong with restart, use it instead of redirect. Currently cloned product will not be displayed in the list until page is refreshed
        // $controller->restart();
        $controller->redirect($controller->fullURL);
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'elements',
        ];
    }
}