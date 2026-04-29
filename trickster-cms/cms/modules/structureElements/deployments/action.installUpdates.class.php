<?php

class installUpdatesDeployments extends structureElementAction
{
    /**
     * @param deploymentsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $installed = $structureElement->installUpdates();
            $url = $structureElement->URL . 'id:' . $structureElement->id
                . '/action:showUpdates/installed:' . (int)$installed . '/';
            if (!$installed && $structureElement->getError()) {
                $url .= 'installError:' . urlencode($structureElement->getError()) . '/';
            }
            $controller->redirect($url);
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [];
    }
}