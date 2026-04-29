<?php

class showUpdatesDeployments extends structureElementAction
{
    /**
     * @param deploymentsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $deployments = [];
            foreach ($structureElement->getUpdates() as $update) {
                $deployments[] = [
                    'type' => $update->type,
                    'version' => $update->version,
                    'description' => $update->description,
                ];
            }
            $renderer->assign('updates', $deployments);
            $renderer->assign('installed', (bool)(int)$controller->getParameter('installed'));
            $renderer->assign('installError', $controller->getParameter('installError'));
            $renderer->assign('contentSubTemplate', 'deployments.updates.tpl');
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [];
    }
}