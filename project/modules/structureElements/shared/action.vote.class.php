<?php

use ZxArt\Voting\VotingService;

class voteShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->executeAction('show');
        if ($structureElement instanceof VotesHolderInterface) {
            if (($value = $controller->getParameter('value')) !== false) {
                $votingService = $this->getService(VotingService::class);
                if ($votingService->vote($structureElement, (int)$value)) {
                    $structureManager->clearElementCache($structureElement->getId());
                }
            }
            $renderer = $this->getService(renderer::class);
            if ($renderer instanceof RendererPluginAppendInterface) {
                $renderer->appendResponseData($structureElement->structureType, $structureElement->getElementData());
            }
        }
    }
}
