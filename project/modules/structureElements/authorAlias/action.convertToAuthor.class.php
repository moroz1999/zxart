<?php

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
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');
            if ($author = $authorsManager->convertAliasToAuthor($structureElement->id)) {
                $controller->redirect($author->getUrl());
            }
        }
    }
}