<?php

use ZxArt\Press\Repositories\PressArticleRepository;

class showPublicFormPressArticle extends structureElementAction
{
    /**
     * @param pressArticleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $pressArticleRepository = $this->getService(PressArticleRepository::class);
        $structureElement->originalContent = $pressArticleRepository->getOriginalContent($structureElement->getId());
        if ($structureElement->originalContent === ''){
            $structureElement->originalContent = $structureElement->content;
        }
        $structureElement->setViewName('form');
    }
}


