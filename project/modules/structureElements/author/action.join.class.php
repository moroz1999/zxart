<?php

use ZxArt\Authors\Services\AuthorsService;

class joinAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $authorsManager = $this->getService(AuthorsService::class);

            if ($structureElement->joinAsAlias) {
                $authorsManager->joinAuthorAsAlias($structureElement->getId(), $structureElement->joinAsAlias);
            }
            if ($structureElement->joinAndDelete) {
                $authorsManager->joinDeleteAuthor($structureElement->getId(), $structureElement->joinAndDelete);
            }
            $structureElement->recalculate();

            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'joinAsAlias',
            'joinAndDelete',
        ];
    }
}


