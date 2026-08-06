<?php

class showPublicFormPressArticle extends structureElementAction
{
    /**
     * @param pressArticleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->originalContent = $structureElement->getOriginalContent();
        $structureElement->setViewName('form');
    }
}
