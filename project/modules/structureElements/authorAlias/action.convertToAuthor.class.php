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
            /**
             * @var AuthorsService $authorsManager
             */
            $authorsManager = $this->getService(AuthorsService::class);
            if ($author = $authorsManager->convertAliasToAuthor($structureElement->id)) {
                $controller->redirect($author->getUrl());
            }
        }
    }
}