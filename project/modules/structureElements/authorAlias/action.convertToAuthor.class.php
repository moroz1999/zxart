<?php

use ZxArt\Authors\Services\AuthorsService;

class convertToAuthorAuthorAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $authorsManager = $this->getService(AuthorsService::class);
            if ($author = $authorsManager->convertAliasToAuthor($structureElement->getId())) {
                $controller->redirect($author->getUrl());
            }
        }
    }
}