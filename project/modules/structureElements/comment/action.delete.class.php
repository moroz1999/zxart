<?php

use ZxArt\Comments\CommentsService;

class deleteComment extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param commentElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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
