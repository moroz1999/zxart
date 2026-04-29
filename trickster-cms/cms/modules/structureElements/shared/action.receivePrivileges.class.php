<?php

class receivePrivilegesShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->privilegesForm = $structureManager->createElement('privileges', 'receiveRelations', $structureElement->id)
        ) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'shared.privileges.tpl');
        }
    }
}
