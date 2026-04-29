<?php

class showActionsLog extends structureElementAction
{
    protected $actionsLogData;

    /**
     * @param actionsLogElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'actionsLog.tpl');
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'elementId',
            'elementType',
            'elementName',
            'periodStart',
            'periodEnd',
            'userId',
            'userIP',
            'action',
        ];
    }
}