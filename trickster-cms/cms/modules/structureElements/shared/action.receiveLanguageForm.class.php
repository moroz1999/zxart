<?php

class receiveLanguageFormShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $linksManager = $this->getService(linksManager::class);
            $connectedIds = $linksManager->getConnectedIdList($structureElement->id, 'foreignRelative', 'parent');
            foreach ($connectedIds as &$connectedId) {
                $linksManager->unLinkElements($structureElement->id, $connectedId, 'foreignRelative');
            }
            $linkedElements = array_values($structureElement->formRelativesInput);
            $linkedElements[] = $structureElement->id;
            // unset empty form input
            foreach ($linkedElements as $i => &$linkedElement) {
                if (!$linkedElement) {
                    unset($linkedElements[$i]);
                }
            }
            $this->crossLinkElements($linkedElements);

            $controller->redirect($structureElement->URL . 'id:' . $structureElement->id . '/action:showLanguageForm/');
        }
        $structureElement->executeAction("showForm");
    }

    //we need not only connect current element to form elements, but also connect form elements to each other
    protected function crossLinkElements($linkedElements)
    {
        $linksManager = $this->getService(linksManager::class);
        while ($element1 = array_shift($linkedElements)) {
            foreach ($linkedElements as $element2) {
                $linksManager->linkElements($element1, $element2, 'foreignRelative', true);
            }
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'formRelativesInput',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}

