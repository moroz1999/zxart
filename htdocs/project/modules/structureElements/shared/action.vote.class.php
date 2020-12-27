<?php

class voteShared extends structureElementAction
{
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
                    /**
                     * @var votesManager $votesManager
                     */
                    $votesManager = $this->getService('votesManager');
                    if ($votesManager->vote($structureElement->id, $structureElement->structureType, $value)) {
                        $structureElement->recalculateVotes();
                        $structureElement->setUserVote($value);
                        if ($structureElement->structureType !== 'comment') {
                            $this->getService('eventsLog')->logEvent($structureElement->id, 'vote');
                        }
                        $structureManager->clearElementCache($structureElement->id);
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