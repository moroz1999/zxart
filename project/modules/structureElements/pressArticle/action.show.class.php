<?php

class showPressArticle extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param pressArticleElement $structureElement
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final){
            $structureElement->setViewName('details');
            $controller->redirect($structureElement->externalLink);
        }
    }
}

