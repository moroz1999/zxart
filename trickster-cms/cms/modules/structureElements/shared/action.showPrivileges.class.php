<?php

class showPrivilegesShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->privilegesForm = $structureManager->createElement('privileges', 'showRelations', $structureElement->id)
        ) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'shared.privileges.tpl');
        }
    }
}