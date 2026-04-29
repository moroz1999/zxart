<?php

/**
 * Class receiveLayoutShared
 *
 * @property ConfigurableLayoutsProviderInterface structureElement
 */
class receiveLayoutShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->persistElementData();
            $controller->redirect($structureElement->getUrl('showLayoutForm'));
        }
        $structureElement->executeAction("showForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = $this->structureElement->getLayoutTypes();
    }

    public function setValidators(&$validators)
    {
    }
}