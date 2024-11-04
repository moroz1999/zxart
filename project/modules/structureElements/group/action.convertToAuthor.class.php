<?php

use ZxArt\Authors\Services\AuthorsService;

class convertToAuthorGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupElement $structureElement
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

            if ($newElement = $authorsManager->convertGroupToAuthor($structureElement)) {
                $controller->redirect($newElement->getUrl());
            }
        }

        $structureElement->setViewName('form');
    }
}


