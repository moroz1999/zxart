<?php

use ZxArt\Comments\CommentsService;

class deleteComment extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param commentElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if (!$structureElement->isEditable()) {
            $controller->redirect($structureElement->getInitialTarget()->getUrl());
            return;
        }
        if ($targetElement = $structureElement->getInitialTarget()) {
            $structureElement->deleteElementData();
            $targetElement->recalculateComments();

            $this->getService(CommentsService::class)->clearCommentsCache();

            $controller->redirect($targetElement->getUrl());
        } else {
            $structureElement->deleteElementData();
            $controller->redirect($controller->baseURL);
        }
    }
}
