<?php

class showPressArticle extends structureElementAction
{
    /**
     * @param pressArticleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final){
            $structureElement->setViewName('details');
        }
    }
}

