<?php

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

            $languagesManager = $this->getService('LanguagesManager');
            if ($currentLanguageElement = $structureManager->getElementById($languagesManager->getCurrentLanguageId())) {
                $currentLanguageElement->clearCommentsCache();
            }

            $controller->redirect($targetElement->getUrl());
        } else {
            $structureElement->deleteElementData();
            $controller->redirect($controller->baseURL);
        }
    }
}
