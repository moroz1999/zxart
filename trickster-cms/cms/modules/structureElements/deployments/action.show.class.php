<?php

class showDeployments extends structureElementAction
{
    /**
     * @param deploymentsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'deployments.show.tpl');
            $configManager = $this->getService(ConfigManager::class);
            $installedDeployments = $configManager->get('deployment.deployments');
            $installedDeployments = array_reverse($installedDeployments);
            $renderer->assign('installedDeployments', $installedDeployments);
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [];
    }
}