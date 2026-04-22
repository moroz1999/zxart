<?php

use ZxArt\Authors\Services\AuthorsService;

class convertToAuthorGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param groupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $authorsManager = $this->getService(AuthorsService::class);

            if ($newElement = $authorsManager->convertGroupToAuthor($structureElement)) {
                $controller->redirect($newElement->getUrl());
            }
        }

        $structureElement->setViewName('form');
    }
}


