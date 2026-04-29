<?php

class hideRecordNotFoundLog extends structureElementAction
{
    /**
     * @param notFoundLogElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $recordId = $controller->getParameter('recordId');
        $db = $this->getService('db');
        $updated = $db->table('404_log')->whereId($recordId)->update(['hidden' => 1]);
        $structureElement->executeAction("show");
    }
}