<?php

use ZxArt\Authors\Services\AuthorsService;

class joinAuthorAlias extends structureElementAction
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
            /**
             * @var AuthorsService $authorsManager
             */
            $authorsManager = $this->getService(AuthorsService::class);

            if ($structureElement->joinAndDelete) {
                $authorsManager->joinDeleteAuthor($structureElement->getId(), $structureElement->joinAndDelete);
            }
            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'joinAndDelete',
        ];
    }
}


