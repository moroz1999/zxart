<?php

class showAiFormPressArticle extends structureElementAction
{
    /**
     * @param pressArticleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('aiForm');
    }
}