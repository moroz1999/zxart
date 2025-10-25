<?php

use ZxArt\Press\Repositories\PressArticleRepository;

class showPublicFormPressArticle extends structureElementAction
{
    /**
     * @param pressArticleElement $structureElement
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $pressArticleRepository = $this->getService(PressArticleRepository::class);
        $structureElement->originalContent = $pressArticleRepository->getOriginalContent($structureElement->getId());
        if ($structureElement->originalContent === ''){
            $structureElement->originalContent = $structureElement->content;
        }
        $structureElement->setViewName('form');
    }
}


