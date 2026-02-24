<?php

use ZxArt\Press\Repositories\PressArticleRepository;

class publicReceivePressArticle extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param pressArticleElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();
            $pressArticleRepository = $this->getService(PressArticleRepository::class);
            $pressArticleRepository->saveOriginalContent($structureElement->getId(), $structureElement->originalContent);

            if ($parentElement = $structureElement->getFirstParentElement()) {
                $linksManager = $this->getService(linksManager::class);
                $linksManager->unLinkElements($parentElement->getId(), $structureElement->getId());
                $linksManager->linkElements($parentElement->getId(), $structureElement->getId(), 'prodArticle');
            }

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'externalLink',
            'authors',
            'people',
            'software',
            'groups',
            'parties',
            'tunes',
            'pictures',
            'introduction',
            'content',
            'originalContent',
            'allowComments',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}

