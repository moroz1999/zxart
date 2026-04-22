<?php

use ZxArt\Authors\Services\AuthorsService;

class convertToAuthorAuthorAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param authorAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $authorsManager = $this->getService(AuthorsService::class);
            if ($author = $authorsManager->convertAliasToAuthor($structureElement->getId())) {
                $controller->redirect($author->getUrl());
            }
        }
    }
}