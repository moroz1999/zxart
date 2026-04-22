<?php

use ZxArt\Authors\Services\AuthorsService;

class joinAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param authorElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
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


