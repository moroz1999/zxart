<?php

use App\Logging\EventsLog;
use votesManager;

class voteShared extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->executeAction('show');
        if ($structureElement instanceof VotesHolderInterface) {
            if (($value = $controller->getParameter('value')) !== false) {
                $value = intval($value);
                $validated = false;
                if ($structureElement->structureType == 'comment' && $value === 1 || $value === -1) {
                    $validated = true;
                } elseif ($value >= 0 && $value <= 5) {
                    $validated = true;
                }

                if ($validated) {
                    $votesManager = $this->getService(votesManager::class);
                    if ($votesManager->vote($structureElement->getId(), $structureElement->structureType, $value)) {
                        $structureElement->recalculateVotes();
                        $structureElement->setUserVote($value);
                        if ($structureElement->structureType !== 'comment') {
                            $this->getService(EventsLog::class)->logEvent($structureElement->getId(), 'vote');
                        }
                        $structureManager->clearElementCache($structureElement->getId());
                    }
                }
            }
            $renderer = $this->getService('renderer');
            if ($renderer instanceof rendererPluginAppendInterface) {
                $renderer->appendResponseData($structureElement->structureType, $structureElement->getElementData());
            }
        }
    }
}